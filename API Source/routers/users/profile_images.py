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
check_if_restricted: Checks if user has been restricted by a moderator/admin
Requests: UID

Returns boolean
"""
async def check_if_restricted( UID: int):
    query = """SELECT * FROM usersextended
                WHERE UID = %s"""
    tuple = (UID,)
    results = sql_methods.execute_read_query(query, tuple)
    if results:
        user = results[0]
        if user[4] == 0:
            return False
        else:
            return True
    else:
        raise HTTPException(status_code=404, detail="User not found.") 
"""
change_profile_img: Changes profile image in every relevant table.
Requests: new image url, token

Returns status code
"""
@router.patch("/image", status_code=204)
async def change_profile_img( img_url: Optional[str] = Query(..., regex="^https:\/\/imgur.com\/+[\w\d]+.png$"), current_user: UserFull = Depends(get_current_user)):
    check = await check_if_restricted(current_user['UID'])
    if check:
        raise HTTPException(status_code=403, detail="Restricted.")
    else:
        queries = []
        datasets = []
        #First query into users table
        query1 = """UPDATE users 
                    SET img_url = %s
                    WHERE UID = %s"""
        data1 = (img_url, current_user['UID'])
        queries.append(query1)
        datasets.append(data1)
        #Second query into topics table
        query2 = """UPDATE topics
                    SET creator_img_url = %s
                    WHERE UID = %s"""
        data2 = (img_url, current_user['UID'])
        queries.append(query2)
        datasets.append(data2)
        #Third query into posts table
        query3 = """UPDATE posts
                    SET creator_img_url = %s 
                    WHERE UID = %s"""
        data3 = (img_url, current_user['UID'])
        queries.append(query3)
        datasets.append(data3)
        #Execute queries
        status = sql_methods.execute_transaction(queries, datasets)
        if status != "Success":
            raise HTTPException(status_code=400, detail="Change failed.")
            
"""
remove_profile_img: Resets profile image for every related table.
Requests: token

Returns status code
"""
@router.delete("/image", status_code=204)
async def remove_profile_img(current_user: UserFull = Depends(get_current_user)):
    queries = []
    datasets = []
    #First query into users table
    query1 = """UPDATE users 
                SET img_url = %s
                WHERE UID = %s"""
    data1 = (None, current_user['UID'])
    queries.append(query1)
    datasets.append(data1)
    #Second query into topics table
    query2 = """UPDATE topics
                SET creator_img_url = %s
                WHERE UID = %s"""
    data2 = (None, current_user['UID'])
    queries.append(query2)
    datasets.append(data2)
    #Third query into posts table
    query3 = """UPDATE posts
                SET creator_img_url = %s 
                WHERE UID = %s"""
    data3 = (None, current_user['UID'])
    queries.append(query3)
    datasets.append(data3)
    #Execute queries
    status = sql_methods.execute_transaction(queries, datasets)
    if status != "Success":
        raise HTTPException(status_code=400, detail="Change failed.")