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
change_password: Change a user's password using their current password as verification
Requests: new username, current_password, token

Returns status code
"""
@router.patch("/privacy", status_code=204)
async def change_privacy_option(privacy: int, current_user: UserFull = Depends(get_current_user)):
    #Query into usersExtended table and change public setting
    query = """UPDATE usersextended
            SET public = %s
            WHERE UID = %s"""
    tuple = (privacy, current_user['UID'])
    #Execute query
    status = sql_methods.execute_query(query, tuple)
    if status != "Success":
        raise HTTPException(status_code=400, detail="Change Failed.")