import os, time
from datetime import datetime, timedelta
from dotenv import load_dotenv
from typing import Optional
from passlib.context import CryptContext
from jose import JWTError, jwt

from fastapi import Depends, HTTPException, status, BackgroundTasks
from fastapi.security import OAuth2PasswordBearer
from pydantic import BaseModel

import mysql.connector
from custom_packages import sql_methods

##############CODE TO BE USED ELSEWHERE IN API##################

load_dotenv()
secret_key = os.getenv('SECRET_KEY')
refresh_key = os.getenv('REFRESH_KEY')
algorithm = 'HS256'

oauth2_scheme = OAuth2PasswordBearer(tokenUrl='/token/')

connection = sql_methods.create_connection()

password_context = CryptContext(schemes=['bcrypt'], deprecated='auto')

#Basic User template for limited use cases
class UserBasic(BaseModel):
    UID: int
    username: str
    img_url: Optional[str] = None

#User Model including extra for a user's personal use
class UserFull(BaseModel):
    UID: int
    username: str
    email: str
    date_joined: datetime
    verified: int
    scope: str
    first_name: Optional[str] = None
    last_name: Optional[str] = None
    mobile_number: Optional[str] = None
    img_url: Optional[str] = None
    public: int
    
#Token Model detailing shared token structure acrossed the API.
class Token(BaseModel):
    access_token: str
    refresh_token: Optional[str] = None
    token_type: str
    
#Token Data Model detailing shared data structure for tokens acrossed the API.
class TokenData(BaseModel):
    UID: Optional[int] = None
    
#UserData Data Model detailing shared user information structure across the API (includes password). Do not use the model as a response.
class UserSecurity(UserBasic):
    timestamp: Optional[datetime] = None
    failed_attempts: Optional[int] = None
    
"""
hashing_pw: hashes the password for security purposes
Requests: password

Using passlib and utilizing bcrypt, a password is encrypted 
Encyrpted password returned.
"""
async def hashing_pw(password: str):
    return password_context.hash(password)
    
"""
password_verification: verifies if plaintext password matches with hashed password
Requests: hashed password, plaintext_password

Returns boolean
"""
def password_verify(hashed_password, plaintext_password):
    return password_context.verify(plaintext_password, hashed_password)
    
"""
grab_user_information_from_db: grabs information of user from users and usersExtended tables in the database
Requests: UID

Will return the results of the query
"""
async def grab_user_information_from_db(username: Optional[str] = None, UID: Optional[int] = None):
    #set up query and tuple data
    if username:
        query = """SELECT * FROM users 
                JOIN usersextended 
                ON  users.UID = usersextended.UID
                JOIN userlocked
                ON users.UID = userlocked.UID
                WHERE users.username = %s"""
        tuple = (username,)
        #Execute queries
        return sql_methods.execute_read_query(query, tuple)
    elif UID:
        query = """SELECT * FROM users 
                JOIN usersextended 
                ON  users.UID = usersextended.UID
                JOIN userlocked
                ON users.UID = userlocked.UID
                WHERE users.UID = %s"""
        tuple = (UID,)
        #Execute queries
        return sql_methods.execute_read_query(query, tuple)
    else: 
        raise HTTPException(status_code=400, detail="Missing username/UID input.")
        
"""
remove_failed_attempts: removes data from database after a set amount of time as a background task/instantaneous
"""
def remove_failed_attempts(UID: Optional[int] = None):
    if UID:
        #set up query and tuple data
        query = """UPDATE userlocked
            SET failed_attempts = %s,
            timestamp = %s
            WHERE UID = %s"""
        tuple = (0, None, UID)
        #Execute queries
        sql_methods.execute_query(query, tuple)
    else:
        #set up query and tuple data
        query = """UPDATE userlocked
                SET failed_attempts = %s,
                timestamp = %s
                WHERE timestamp < %s"""
        tuple = (0, None, datetime.utcnow())
        #Execute queries
        sql_methods.execute_query(query, tuple)
    
"""
increment_failed_attempts: increases failed attempts in database when password is wrong.
Requests: UID
"""
def increment_failed_attempts(UID: int, failed_attempts: int):
    #set up query and tuple data
    timestamp = datetime.utcnow() + timedelta(minutes=5)
    query = """UPDATE userlocked
            SET failed_attempts = %s,
            timestamp = %s
            WHERE UID = %s"""
    tuple = ((failed_attempts + 1),timestamp, UID)
    #Execute queries
    sql_methods.execute_query(query, tuple)
    
"""
user_data_extended_modeled: grabs information from database and creates a dict with it
Requests: UID

Returns dict of user information
"""
async def user_data_dict(username: Optional[str] = None, UID: Optional[int] = None):
    if username:
        results = await grab_user_information_from_db(username=username)
    elif UID:
        results = await grab_user_information_from_db(UID=UID)
    else:
        raise HTTPException(status_code=400, detail="Missing username/UID input.")
    if results:
        user = results[0]
        user_data = {'UID':user[0],
                     'username' : user[1],
                     'hashed_password' : user[2],
                     'email' : user[3],
                     'date_joined' : user[4],
                     'img_url' : user[5],
                     'verified' : user[6],
                     'scope' : user[7],
                     'first_name' : user[9],
                     'last_name' : user[10],
                     'mobile_number' : user[11],
                     'restricted' : user[12],
                     'public' : user[13],
                     'timestamp' : user[16],
                     'failed_attempts' : user[17]}
    else:
        raise HTTPException(status_code=404, detail="User with specified username/UID not found.")
    return user_data
    
"""
authenticate_user: returns user info from database using User model
Requests: username, password

Authenticates user by comparing hashed password with hashed password saved in database
Returns User Model
"""
async def authenticate_user(username: str, password: str):
    remove_failed_attempts()
    user_info = await user_data_dict(username)
    if user_info['failed_attempts'] > 4:
        raise HTTPException(status_code=403, detail="Locked Account.")
    if not user_info:
        raise HTTPException(status_code=404, detail="Incorrect login information.")
    if not password_verify(user_info['hashed_password'], password):
        increment_failed_attempts(user_info['UID'], user_info['failed_attempts'])
        raise HTTPException(status_code=400, detail="Incorrect login information.")
    user_data = UserBasic(**user_info)
    remove_failed_attempts(user_info['UID'])
    return user_data
    

"""
create_access_token: returns a token 
Requests: data, expiration time

Returns access token
"""
def create_access_token(data: dict, expiration_time: Optional[timedelta] = None):
    encoding_data = data.copy()
    if expiration_time:
        expires = datetime.utcnow() + expiration_time
    else:
        expires = datetime.utcnow() + timedelta(minutes=60)
    encoding_data.update({'iss' : 'thealexandria.api', 'exp' : expires})
    access_token = jwt.encode(encoding_data, secret_key, algorithm=algorithm)
    return access_token
    
"""
create_refresh_token: creates a refresh token so user does not need to keep relogging
Requests: data

Returns refresh token
"""
def create_refresh_token(data: dict):
    encoding_data = data.copy()
    expires = datetime.utcnow() + timedelta(days=30)
    encoding_data.update({'iss' : 'thealexandria.api', 'exp' : expires})
    refresh_token = jwt.encode(encoding_data, refresh_key, algorithm=algorithm)
    return refresh_token
    
"""
refresh_token_regeneration: creates a new access token using refresh token 
Requests: refresh_token, access token expiration time

Returns access token
"""
def refresh_token_regeneration(refresh_token: str, expiration_time: Optional[timedelta] = None):
    try:
        payload = jwt.decode(refresh_token, refresh_key, algorithms=[algorithm])
        UID: str = payload.get("sub")
        if UID is None:
            raise HTTPException(status_code=403, detail="Unable to authenticate.")
    except JWTError:
        raise HTTPException(status_code=403, detail="Unable to authenticate.")
    access_token = create_access_token(data={"sub": UID}, expiration_time=expiration_time)
    return access_token
    
"""
refresh_token_blacklist: a refresh token is blacklisted in the database so it cannot be used to create new access tokens after logging out
Requests: refresh_token

Returns access token
"""
async def refresh_token_blacklist(refresh_token: str):
    try:
        payload = jwt.decode(refresh_token, refresh_key, algorithms=[algorithm])
        expiration: str = payload.get("exp")
        if expiration is None:
            raise HTTPException(status_code=400, detail="Token already expired")
    except JWTError:
        raise HTTPException(status_code=403, detail="Unable to authenticate.")
    date = datetime.fromtimestamp(int(expiration))
    
    #Blacklist in database
    query = """INSERT INTO blacklistedtokens
               (refresh_token, date) 
               VALUES (%s, %s)"""
    tuple = (refresh_token, date)
    #Execute queries
    status = sql_methods.execute_query(query, tuple)
    if status != "Success":
        raise HTTPException(status_code=400, detail="An error occured with the database.")
    

"""
get_current_user: returns user information from token
Requests: access token

Returns user information
"""
async def get_current_user(token: str = Depends(oauth2_scheme)):
    try:
        payload = jwt.decode(token, secret_key, algorithms=[algorithm])
        UID: str = payload.get("sub")
        if UID is None:
            raise HTTPException(status_code=403, detail="Unable to authenticate.")
        token_data = TokenData(UID=int(UID))
    except JWTError:
        raise HTTPException(status_code=403, detail="Unable to authenticate.")
    user = await user_data_dict(UID=token_data.UID)
    if user is None:
        raise HTTPException(status_code=403, detail="Could not authenticate.")
    return user
    
"""
authenticate_librarian_privileges: raises 403 status code if user is not authorized
Requests: access token

Returns status code if not authorized
"""
async def authenticate_librarian_privileges(current_user: UserFull = Depends(get_current_user)):
    authorized = None
    security_scopes = current_user['scope'].split(' ')
    for permissions in security_scopes:
        if permissions == 'TA-Librarian' or permissions == 'TA-Administrator':
            authorized = True
    if authorized == None:
        raise HTTPException(status_code=403, detail="Unauthorized.")

"""
authenticate_moderator_privileges: raises 403 status code if user is not authorized
Requests: token

Returns status code if not authorized
"""
async def authenticate_moderator_privileges(current_user: UserFull = Depends(get_current_user)):
    authorized = None
    security_scopes = current_user['scope'].split(' ')
    for permissions in security_scopes:
        if permissions == 'TA-Moderator' or permissions == 'TA-Administrator':
            authorized = True
    if authorized == None:
        raise HTTPException(status_code=403, detail="Unauthorized.")

"""
authenticate_admin_privileges: raises 403 status code if user is not authorized
Requests: token

Returns status code if not authorized
"""
async def authenticate_admin_privileges(current_user: UserFull = Depends(get_current_user)):
    authorized = None
    security_scopes = current_user['scope'].split(' ')
    for permissions in security_scopes:
        if permissions == 'TA-Administrator':
            authorized = True
    if authorized == None:
        raise HTTPException(status_code=403, detail="Unauthorized.")
