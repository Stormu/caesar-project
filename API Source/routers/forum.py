from typing import Optional, List
from fastapi import APIRouter, Depends, HTTPException, Query
from dependencies import get_current_user, UserFull

from custom_packages import sql_methods
from datetime import datetime, timedelta
from pydantic import BaseModel

router = APIRouter(prefix = "/forum", tags = ["Forum"])

#Topic Post information to show in the forum
class Topics(BaseModel):
    topic_id: int
    UID: int
    date_created: datetime
    posts: int
    title: str
    body: str
    locked: int
    pinned: int
    creator_username: str
    creator_img_url: Optional[str]
    
#Post information to show in the forum under topics
class Posts(BaseModel):
    post_id: int
    topic_id: int
    UID: int
    date_created: datetime
    body: str
    creator_username: str
    creator_img_url: Optional[str]
    
"""
pull_topic_data: grabs data about a topic from the database
Requests: topic_id

returns dictionary of topic data
"""
async def pull_topic_data(topic_id: int):
    query = """SELECT * FROM topics
                WHERE topic_id = %s"""
    tuple = (topic_id,)
    results = sql_methods.execute_read_query(query, tuple)
    if results:
        topic = results[0]
        topic_data = {'topic_id': topic[0], 
                      'UID': topic[1], 
                      'date_created' : topic[2], 
                      'posts' : topic[3], 
                      'title': topic[4], 
                      'body': topic[5], 
                      'locked': topic[6], 
                      'pinned': topic[7], 
                      'creator_username': topic[8],
                      'creator_img_url': topic[9]}
    else:
        raise HTTPException(status_code=404, detail='No topic with specified id found.')
    return topic_data
    
"""
pull_post_data: grabs data about a post from the database
Requests: post_id

returns dictionary of posts data
"""
async def pull_post_data(post_id: int):
    query = """SELECT * FROM posts
                WHERE post_id = %s"""
    tuple = (post_id,)
    results = sql_methods.execute_read_query(query, tuple)
    if results:
        post = results[0]
        post_data = {'post_id': post[0],
                     'topic_id': post[1],
                     'UID' : post[2],
                     'date_created': post[3],
                     'body': post[4],
                     'creator_username': post[5],
                     'creator_img_url': post[6]}
    else:
        raise HTTPException(status_code=404, detail='No post for topic found.')
    return post_data

"""
create_topic: new topic is added into the database
Requests: title, description, body, user

returns status code
"""
@router.post("/topic", status_code=201)
async def create_topic(title: Optional[str] = Query(..., max_length=70), 
                       body: Optional[str] = Query(..., max_length=300), 
                       current_user: UserFull = Depends(get_current_user)):
    if(current_user['restricted'] != 0):
        raise HTTPException(status_code=403, detail='Unauthorized: Restricted user.')
    UID = current_user['UID']
    date_created = datetime.utcnow()
    query = """INSERT INTO topics
            (UID, date_created, title, body, creator_username, creator_img_url) 
            VALUES (%s, %s, %s, %s, %s, %s)"""
    tuple = (UID, date_created, title, body, current_user['username'], current_user['img_url'])
    status = sql_methods.execute_query(query, tuple)
    if status != "Success":
        raise HTTPException(status_code=400, detail='Improper input.')
        
"""
edit_topic: edit topic information in the database
Requests: topic_id

returns status code
"""
@router.patch("/topic", status_code=204)
async def edit_topic(topic_id: int, title: Optional[str], body: Optional[str], current_user: UserFull = Depends(get_current_user)):
    data = await pull_topic_data(topic_id)
    if(current_user['restricted'] != 0):
        raise HTTPException(status_code=403, detail='Unauthorized: Restricted user.')
    if data['UID'] == current_user['UID']:
        query = """UPDATE topics
                SET title = %s,
                body = %s
                WHERE topic_id = %s"""
        tuple = (title, body, topic_id)
        status = sql_methods.execute_query(query, tuple)
        if status != "Success":
            raise HTTPException(status_code=400, detail='Improper input.')
    else:
        raise HTTPException(status_code=403, detail='Unauthorized')
        
"""
get_topic: pulls topics information from database
Requests: topic_id

returns status code
"""
@router.get("/topic", response_model=Topics)
async def get_topic(topic_id: int):
    data = await  pull_topic_data(topic_id)
    return data
    
"""
get_topic: pulls topics that were pinned by a moderator/admin
Requests: nothing

returns status code
"""
@router.get("/pinned", response_model=List[Topics])
async def get_pinned_topics():
    query = """SELECT * FROM topics
                WHERE pinned = 1"""
    tuple = ()
    topics_list = []
    results = sql_methods.execute_read_query(query, tuple)
    for topics in results:
        topics_list.append({'topic_id': topics[0], 
                            'UID': topics[1], 
                            'date_created' : topics[2], 
                            'posts' : topics[3], 
                            'title' : topics[4], 
                            'body': topics[5], 
                            'locked': topics[6], 
                            'pinned': topics[7],
                            'creator_username': topics[8],
                            'creator_img_url': topics[9]})
    if topics_list:
        return topics_list
    else:
        raise HTTPException(status_code=404, detail='No pinned topics found.')
        
"""
get_topic: Retrieves topics from the database in order of newest to oldest. Only 10 at a time. Offset is multiplied by 10.
Requests: offset

returns list of topics
"""
@router.get("/topics", response_model=List[Topics])
async def get_topics(offset: int):
    starting_post = offset*10
    query = """SELECT * FROM topics
                WHERE pinned = 0
                ORDER BY date_created DESC
                LIMIT 10
                OFFSET %s"""
    tuple = (starting_post,)
    topics_list = []
    results = sql_methods.execute_read_query(query, tuple)
    for topics in results:
        topics_list.append({'topic_id': topics[0], 
                            'UID': topics[1], 
                            'date_created' : topics[2], 
                            'posts' : topics[3], 
                            'title' : topics[4], 
                            'body': topics[5], 
                            'locked': topics[6], 
                            'pinned': topics[7],
                            'creator_username': topics[8],
                            'creator_img_url': topics[9]})
    if topics_list:
        return topics_list
    else:
        raise HTTPException(status_code=404, detail='No unpinned topics found.')
    
"""
create_topic: new topic is added into the database
Requests: title, description, body, token

returns status code
"""
@router.post("/post", status_code=201)
async def create_post(topic_id: int, body: str, current_user: UserFull = Depends(get_current_user)):
    if(current_user['restricted'] != 0):
        raise HTTPException(status_code=403, detail='Unauthorized: Restricted user.')
    UID = current_user['UID']
    date_created = datetime.utcnow()
    queries = []
    datasets = []
    #First query, insert into posts
    query1 = """INSERT INTO posts
            (topic_id, UID, date_created, body, creator_username, creator_img_url) 
            VALUES (%s, %s, %s, %s, %s, %s)"""
    data1 = (topic_id, UID, date_created, body, current_user['username'], current_user['img_url'])
    queries.append(query1)
    datasets.append(data1)
    #Second query, update posts count in topic table
    query2 = """UPDATE topics
               SET posts = posts + 1
               WHERE topic_id = %s"""
    data2 = (topic_id,)
    queries.append(query2)
    datasets.append(data2)
    #Execute queries
    status = sql_methods.execute_transaction(queries, datasets)
    if status != "Success":
        raise HTTPException(status_code=400, detail="Failed input.")
        
"""
get_post: pulls a post's information from database
Requests: post_id

returns info of the post
"""
@router.get("/post", response_model=Posts)
async def get_post(post_id: int):
    data = await pull_post_data(post_id)
    return data
    
"""
get_posts: gets posts from database for a specific topic. Only 10 at a time. Offset is multiplied by 10.
Requests: topic_id, offset

returns list of posts
"""
@router.get("/posts", response_model=List[Posts])
async def get_posts(topic_id: int, offset: int):
    starting_post = offset*10
    query = """SELECT * FROM posts
            WHERE topic_id = %s
            ORDER BY date_created ASC
            LIMIT 10
            OFFSET %s"""
    tuple = (topic_id, starting_post)
    posts_list = []
    results = sql_methods.execute_read_query(query, tuple)
    for posts in results:
        posts_list.append({'post_id': posts[0],
                     'topic_id': posts[1],
                     'UID' : posts[2],
                     'date_created': posts[3],
                     'body': posts[4],
                     'creator_username': posts[5],
                     'creator_img_url': posts[6]})
    if posts_list:
        return posts_list
    else:
        raise HTTPException(status_code=404, detail='No posts for this topic found.')
    
"""
remove_post: deletes post from database
Requests: post_id

returns status code
"""
@router.delete("/post", status_code=204)
async def remove_post(post_id: int, current_user: UserFull = Depends(get_current_user)):
    data = await pull_post(post_id)
    if(data['UID'] == UserFull['UID']):
        query = "DELETE FROM posts WHERE post_id = %s"
        tuple = (post_id,)
        status = sql_methods.execute_query(query, tuple)
        if status != "Success":
            raise HTTPException(status_code=400, detail='Failed to delete.')
    else:
        raise HTTPException(status_code=403, detail='Unauthorized.')