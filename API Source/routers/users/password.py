from typing import Optional
from fastapi import APIRouter, Depends, HTTPException, Query
from dependencies import (get_current_user, 
                          UserFull,
                          hashing_pw,
                          password_verify)

from custom_packages import sql_methods
from datetime import datetime, timedelta

router = APIRouter(prefix = "/users", tags = ["Users"])

"""
authenticate_password: verifies current password is correct
Requests: new username, current_password

Returns status code
"""
def authenticate_password(current_password: str, hashed_password: str):
    return password_verify(hashed_password, current_password)

"""
change_password: Change a user's password using their current password as verification
Requests: new username, current_password, token

Returns status code
"""
@router.patch("/password", status_code=204)
async def change_password( current_password: str,
                           new_password: Optional[str] = Query(..., min_length=3, max_length=50, regex="^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\!\-\?\$\#])([\!\-\?\$\#\w]+){12,50}$"),
                           current_user: UserFull = Depends(get_current_user)):
    if authenticate_password(current_password, current_user['hashed_password']):
        hashed_password = await hashing_pw(new_password)
        #Query into users table to change hashed_password
        query = """UPDATE users 
                    SET hashed_password = %s
                    WHERE UID = %s"""
        tuple = (hashed_password, current_user['UID'])
        #Execute queries
        status = sql_methods.execute_query(query, tuple)
        if status != "Success":
            raise HTTPException(status_code=400, detail="Change Failed.")
    else:
        raise HTTPException(status_code=403, detail="Wrong Password.")