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
remove_expired_restrictions: Removes any user who has updated their username more than 30 days ago.
"""
async def remove_expired_restrictions():
    current_time = datetime.utcnow()
    query = "DELETE FROM namechange WHERE restriction_time < %s"
    tuple = (current_time,)
    results = sql_methods.execute_query(query, tuple)

"""
check_if_restricted: Checks if user has changed their username in the last 30 days.
Requests: UID

Returns boolean
"""
async def check_if_restricted( UID: int):
    await remove_expired_restrictions()
    query = """SELECT * FROM namechange
                WHERE UID = %s"""
    tuple = (UID,)
    results = sql_methods.execute_read_query(query, tuple)
    if results:
        return True
    else:
        return False
"""
change_username: Changes username in every related table. It also adds new information to the database
restricting user from changing their username for 30 days.
Requests: new username, token

Returns status code
"""
@router.patch("/username", status_code=204)
async def change_username( username: Optional[str] = Query(..., min_length=3, max_length=15, regex="^[a-zA-Z-\d_]{3,15}$"), current_user: UserFull = Depends(get_current_user)):
    check = await check_if_restricted(current_user['UID'])
    if check:
        raise HTTPException(status_code=403, detail="Restricted Change.")
    else:
        queries = []
        datasets = []
        #First query into users table
        query1 = """UPDATE users 
                    SET username = %s
                    WHERE UID = %s"""
        data1 = (username, current_user['UID'])
        queries.append(query1)
        datasets.append(data1)
        #Second query into userlocked table
        query2 = """UPDATE userlocked
                    SET username = %s 
                    WHERE UID = %s"""
        data2 = (username, current_user['UID'])
        queries.append(query2)
        datasets.append(data2)
        #Third query into topics table
        query3 = """UPDATE topics
                    SET creator_username = %s
                    WHERE UID = %s"""
        data3 = (username, current_user['UID'])
        queries.append(query3)
        datasets.append(data3)
        #Fourth query into posts table
        query4 = """UPDATE posts
                    SET creator_username = %s 
                    WHERE UID = %s"""
        data4 = (username, current_user['UID'])
        queries.append(query4)
        datasets.append(data4)
        #Fifth query into namechange table
        time = datetime.utcnow() + timedelta(days=30)
        query5 = """INSERT INTO namechange
                   (UID, restriction_time) 
                   VALUES (%s, %s)"""
        data5 = (current_user['UID'], time)
        queries.append(query5)
        datasets.append(data5)
        #Execute queries
        status = sql_methods.execute_transaction(queries, datasets)
        if status != "Success":
            raise HTTPException(status_code=400, detail="Username Taken.")