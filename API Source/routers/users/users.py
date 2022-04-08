from typing import Optional
from fastapi import APIRouter, Depends, HTTPException, Query
from dependencies import (hashing_pw,
                          get_current_user, 
                          UserFull, 
                          authenticate_admin_privileges,
                          user_data_dict)

from custom_packages import sql_methods
from datetime import datetime
from pydantic import BaseModel

router = APIRouter(prefix = "/users", tags = ["Users"])

#Basic User template to be returned for unique webpage features
class UserBasic(BaseModel):
    UID: int
    username: str
    img_url: Optional[str] = None
    
#User Model returned if user has their profile set to private
class UserPublicInfo0(BaseModel):
    UID: int
    username: str
    date_joined: datetime
    img_url: Optional[str] = None
    
#User Model returned if user has their profile set to public
class UserPublicInfo1(BaseModel):
    UID: int
    username: str
    date_joined: datetime
    first_name: Optional[str] = None
    last_name: Optional[str] = None
    email: Optional[str] = None
    img_url: Optional[str] = None
    
    
"""
user_data_extended_modeled: changes data for a user in the database
Requests: UID

Returns status of change
"""
async def change_user_data(data: UserFull, 
                           username: Optional[str] = None, 
                           password: Optional[str] = Query(None, min_length=12, max_length=50), 
                           email: Optional[str] = Query(None, regex="[^@\s]+@[^@\s]+\.[^@\s]+"), 
                           first_name: Optional[str] = None, 
                           last_name: Optional[str] = None,
                           mobile_number: Optional[str] = None):
    if username:
        data['username'] = username
    if password:
        hashed_password = await hashing_pw(password)
        data['hashed_password'] = password
    if email:
        data['email'] = email
    if first_name:
        data['first_name'] = first_name
    if last_name:
        data['last_name'] = last_name
    if mobile_number:
        data['mobile_number'] = mobile_number
        
    queries = []
    datasets = []
    #First query into users table
    query1 = """UPDATE users 
                SET username = %s, 
                hashed_password = %s, 
                email = %s 
                WHERE UID = %s"""
    data1 = (data['username'], data['hashed_password'], data['email'], data['UID'])
    queries.append(query1)
    datasets.append(data1)
    #Second query into usersExtended table
    query2 = """UPDATE usersextended
                SET first_name = %s,
                last_name = %s,
                mobile_number = %s
                WHERE UID = %s"""
    data2 = (data['first_name'], data['last_name'], data['mobile_number'], data['UID'])
    queries.append(query2)
    datasets.append(data2)
    #Execute queries
    return sql_methods.execute_transaction(queries, datasets)
    
"""
remove_user_from_db: removes user from database
Requests: UID

Will delete user information related to the UID in both users and usersExtended tables
"""
async def remove_user_from_db(UID: int):
    #set up queries and data for transaction
    datasets = [(UID,),(UID,)]
    query1 = "DELETE FROM users WHERE UID = %s"
    query2 = "DELETE FROM usersextended WHERE UID = %s"
    #Execute queries
    return sql_methods.execute_transaction(queries, datasets)
  
"""
check_if_username_taken: returns if the username is found in the database
Requests: username

Returns boolean if the username exists
"""  
def check_if_username_taken(username: str):\
    #Setting up query and executing
    query = """SELECT * FROM users
            WHERE username = %s"""
    tuple = (username,)
    results = sql_methods.execute_read_query(query, tuple)
    if results:
        return True
    else:
        return False

"""
check_if_email_taken: returns if the email is found in the database
Requests: email

Returns boolean if the email exists
"""
def check_if_email_taken(email: str):
    #Setting up query and executing
    query = """SELECT * FROM users
            WHERE email = %s"""
    tuple = (email,)
    results = sql_methods.execute_read_query(query, tuple)
    if results:
        return True
    else:
        return False
    
    
"""
add_user: adds user information to users and usersExtended tables
Requests: username, email, password
Optional requests: first_name, last_name, mobile_number

dateJoined recorded as datetime object using current time
default scope is 'TA-Basic'
UID is incremented automatically by the database for both tables
"""
@router.post("/", status_code=201)
async def add_user(username: Optional[str] = Query(..., min_length=3, max_length=15, regex="^[a-zA-Z-\d_]{3,15}$"), 
                   email: Optional[str] = Query(..., regex="[^@\s]+@[^@\s]+\.[^@\s]+"), 
                   password: Optional[str] = Query(..., min_length=12, max_length=50, regex="^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\!\-\?\$\#])([\!\-\?\$\#\w]+){12,50}$"),
                   first_name: Optional[str] = Query(None, min_length=2, max_length=15), 
                   last_name: Optional[str] = Query(None, min_length=2, max_length=15),
                   mobile_number: Optional[str] = None):
    if check_if_username_taken(username):
        raise HTTPException(status_code=400, detail="Username Taken.")
    if check_if_email_taken(email):
        raise HTTPException(status_code=400, detail="Email Taken.")
    dateJoined = datetime.utcnow()
    hashed_password = await hashing_pw(password)
    queries = []
    datasets = []
    #First query into users table
    query1 = """INSERT INTO users 
               (username, hashed_password, email, date_joined, scope) 
               VALUES (%s, %s, %s, %s, 'TA-Basic')"""
    data1 = (username, hashed_password, email, dateJoined)
    queries.append(query1)
    datasets.append(data1)
    #Second query into usersExtended table
    query2 = """INSERT INTO usersextended
               (first_name, last_name, mobile_number) 
               VALUES (%s, %s, %s)"""
    data2 = (first_name, last_name, mobile_number)
    queries.append(query2)
    datasets.append(data2)
    #Third query into usersLocked to add failed login details to the table
    query3 = """INSERT INTO userlocked
               (username) 
               VALUES (%s)"""
    data3 = (username,)
    queries.append(query3)
    datasets.append(data3)
    #Execute queries
    status = sql_methods.execute_transaction(queries, datasets)
    if status != "Success":
        raise HTTPException(status_code=400, detail="Failed to create profile.")
            
"""
get_basic_information: returns basic information of user from database
Requests: username

Returns UID, username, profile image url (if any)
"""
@router.get("/basic", response_model=UserBasic)
async def get_basic_information(UID: int):
    query = """SELECT * FROM users
                WHERE UID = %s"""
    tuple = (UID,)
    results = sql_methods.execute_read_query(query, tuple)
    if results:
        user = results[0]
        user_data = {'UID': user[0], 'username': user[1], 'img_url': user[5]}
    return user_data
    
"""
get_public_user_info: returns user info from database, changes depending on if user allows information to be public
Requests: UID

Returns information from the database about the user
"""
@router.get("/")
async def get_public_user_information(username: Optional[str] = None, UID: Optional[int] = None):
    if username:
        data = await user_data_dict(username=username)
    elif UID:
        data = await user_data_dict(UID=UID)
    else:
        raise HTTPException(status_code=400, detail="Missing username or UID.")
    if data['public'] == 1:
        user_data = UserPublicInfo1(**data)
    else:
        user_data = UserPublicInfo0(**data)
    return user_data

"""
update_user_info: updates a user's information in the database using UID; must be authorized
Requests: UID, token for authorization
Optional: username, password, email, first_name, last_name, mobile_number

Returns status code
"""
@router.patch("/", status_code=204)
async def update_user_information(UID: int, 
                                  authorized: bool = Depends(authenticate_admin_privileges), 
                                  username: Optional[str] = Query(None, min_length=3, max_length=15), 
                                  password: Optional[str] = Query(None, min_length=12, max_length=50), 
                                  email: Optional[str] = Query(None, regex="[^@\s]+@[^@\s]+\.[^@\s]+"), 
                                  first_name: Optional[str] = None, 
                                  last_name: Optional[str] = None,
                                  mobile_number: Optional[str] = None):
    data = await user_data_dict(UID=UID)
    status = await change_user_data(data, username, password, email, first_name, last_name, mobile_number)
    if status != "Success":
        raise HTTPException(status_code=400, detail="Failed to update.")

"""
delete_user: removes user from database; deletes user account
Requests: username, token for authorization

username information will be wiped from the database after username is found. 
"""
@router.delete("/" , status_code=204)
async def remove_users(UID: int, authorized: bool = Depends(authenticate_admin_privileges)):
    status = remove_user_from_db(UID)
    if status != "Success":
        raise HTTPException(status_code=400, detail="Failed to remove.")
    
"""
current_user_information: pulls current user information using token
Requests: token

Returns user information
"""
@router.get("/current", response_model=UserFull)
async def current_user_info(current_user: UserFull = Depends(get_current_user)):
    return current_user
   
#Deprecated due to new API endpoints for user editting
"""
update_user_info: pulls current user information using token
Requests: token

Returns status code
"""
"""
@router.patch("/current")
async def update_current_user(current_user: UserFull = Depends(get_current_user), 
                              username: Optional[str] = Query(None, min_length=3, max_length=15), 
                              password: Optional[str] = Query(None, min_length=12, max_length=50), 
                              email: Optional[str] = Query(None, regex="[^@\s]+@[^@\s]+\.[^@\s]+"), 
                              first_name: Optional[str] = None, 
                              last_name: Optional[str] = None,
                              mobile_number: Optional[str] = None):
    status = await change_user_data(current_user, username, password, email, first_name, last_name, mobile_number)
    if status != "Success":
        raise HTTPException(status_code=400, detail="Failed to update.")
"""

"""
remove_current_user: user removes self from database using token
Requests: token

User deletes their account off the database along with their information
"""
@router.delete("/current", status_code=204)
async def remove_current_user(current_user: UserFull = Depends(get_current_user)):
    status = remove_user_from_db(current_user.UID)
    if status != "Success":
        raise HTTPException(status_code=400, detail="Failed to remove.")
        
