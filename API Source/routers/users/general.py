from typing import Optional
from fastapi import APIRouter, Depends, HTTPException, Query
from dependencies import (get_current_user, 
                          UserFull, 
                          authenticate_admin_privileges,
                          user_data_dict)

from custom_packages import sql_methods
from datetime import datetime, timedelta

router = APIRouter(prefix = "/users", tags = ["Users"])

"""
update_general_information: Changes specified information provided by the user.
Requests: token
Optional: email, first_name, last_name, mobile_number

Returns status code
"""
@router.patch("/general", status_code=204)
async def update_general_information(email: Optional[str] = Query(None, regex="[^@\s]+@[^@\s]+\.[^@\s]+"),
                                     first_name: Optional[str] = Query(None, max_length=20, regex="^[a-zA-Z\s]*$"), 
                                     last_name: Optional[str] = Query(None, max_length=20, regex="^[a-zA-Z\s]*$"),
                                     mobile_number: Optional[str] = Query(None, max_length=15, regex="[\d\w]"),
                                     current_user: UserFull = Depends(get_current_user)):
    if email:
        current_user['email'] = email;
    if first_name:
        if first_name == " ":
            current_user['first_name'] = None;
        else:
            current_user['first_name'] = first_name;
    if last_name:
        if last_name == " ":
            current_user['last_name'] = None;
        else:
            current_user['last_name'] = last_name;
    if mobile_number:
        if mobile_number == " ":
            current_user['mobile_number'] = None;
        else:
            current_user['mobile_number'] = mobile_number;
        
    queries = []
    datasets = []
    #Set up query into users tables
    query1 = """UPDATE users
            SET email = %s
            WHERE UID = %s"""
    data1 = (current_user['email'], current_user['UID'])
    queries.append(query1)
    datasets.append(data1)
    #Set up query into usersExtended table
    query2 = """UPDATE usersextended
            SET first_name = %s,
            last_name = %s,
            mobile_number = %s
            WHERE UID = %s"""
    data2 = (current_user['first_name'], current_user['last_name'], current_user['mobile_number'], current_user['UID'])
    queries.append(query2)
    datasets.append(data2)
    #Execute queries
    status = sql_methods.execute_transaction(queries, datasets)
    if status != "Success":
        raise HTTPException(status_code=403, detail="Email Taken.")