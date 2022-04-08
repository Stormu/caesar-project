from typing import Optional, List
from fastapi import APIRouter, Depends, HTTPException
from dependencies import authenticate_librarian_privileges

from custom_packages import sql_methods
from datetime import datetime, timedelta, timezone
from pydantic import BaseModel

router = APIRouter(prefix = "/events", tags = ["Events"])

#Event information template to be returned from API calls
class Events(BaseModel):
    event_id: int
    event_name: str
    date: datetime
    location: Optional[str] = None
    description: Optional[str] = None
    
async def fetch_event_data(event_id: int):
    query = """SELECT * FROM events
                WHERE event_id = %s"""
    tuple = (event_id,)
    results = sql_methods.execute_read_query(query, tuple)
    if results:
        event = results[0]
        event_data = {'event_id': event[0], 'event_name': event[1], 'date' : event[2], 'location': event[3], 'description': event[4]}
    else:
        raise HTTPException(status_code=404, detail='No event found.')
    return event_data

"""
get_event: gets specific event with id from database
Requests: event_id

returns dictionary of event information
""" 
@router.get("/", response_model=Events)      
async def get_event(event_id: int):
    return await fetch_event_data(event_id)

"""
add_event: adds an event into the database
Requests: event_name, date, authorized access
Optional requests: location, description

returns status code
"""
@router.post("/", status_code=201)
async def add_event(event_name: str, date: datetime, location: Optional[str] = None, description: Optional[str] = None, authorized: bool = Depends(authenticate_librarian_privileges)):
    query = """INSERT INTO events
            (event_name, date, location, description) 
            VALUES (%s, %s, %s, %s)"""
    tuple = (event_name, date, location, description)
    status = sql_methods.execute_query(query, tuple)
    if status != "Success":
        raise HTTPException(status_code=400, detail='Improper input.')
        
"""
add_event: adds an event into the database
Requests: event_name, date, authorized access
Optional requests: location, description

returns status code
"""
@router.patch("/", status_code=201)
async def update_event(event_id: int, 
                       event_name: Optional[str] = None, 
                       date: Optional[datetime] = None, 
                       location: Optional[str] = None, 
                       description: Optional[str] = None, 
                       authorized: bool = Depends(authenticate_librarian_privileges)):
    event_data = await fetch_event_data(event_id)
    
    #Check changes and apply to event data
    if event_name:
        event_data['event_name'] = event_name
    if date:
        event_data['date'] = date
    if location:
        event_data['location'] = location
    if description:
        event_data['description'] = description
        
    query = """UPDATE events
            SET event_name = %s,
            date = %s,
            location = %s,
            description = %s 
            WHERE event_id = %s"""
    tuple = (event_data['event_name'], event_data['date'], event_data['location'], event_data['description'], event_id)
    status = sql_methods.execute_query(query, tuple)
    if status != "Success":
        raise HTTPException(status_code=400, detail='Improper input.')
        
"""
remove_event: deletes an event from the database
Requests: event_id

returns status code
"""
@router.delete("/", status_code=204) 
async def remove_event(event_id: int, authorized: bool = Depends(authenticate_librarian_privileges)):
    query = "DELETE FROM events WHERE event_id = %s"
    tuple = (event_id,)
    status = sql_methods.execute_query(query, tuple)
    if status != "Success":
        raise HTTPException(status_code=400, detail='Failed to remove.')
        
"""
get_events: gets events from a specified day
Requests: day, month, year

returns list of events using Events model
""" 
@router.get("/date", response_model=List[Events])      
async def get_events(day : int, month: int, year: int):
    first_time = datetime(year=year, month=month, day=day, tzinfo=timezone.utc) - timedelta(seconds=1)
    last_time = datetime(year=year, month=(month+1), day=day, tzinfo=timezone.utc) + timedelta(seconds=1)
    events_list = []
    query = """SELECT * FROM events
                WHERE date BETWEEN %s AND %s"""
    tuple = (first_time,last_time)
    results = sql_methods.execute_read_query(query, tuple)
    for event in results:
        events_list.append({'event_id': event[0], 'event_name': event[1], 'date' : event[2], 'location': event[3], 'description': event[4]})
    if events_list:
        return events_list
    else:
        raise HTTPException(status_code=404, detail='No event found.')
    
"""
get_events: gets events from a specified month and year
Requests: month, year

returns list of events using Events model
""" 
@router.get("/month", response_model=List[Events])      
async def get_events(month: int, year: int):
    first_time = datetime(year=year, month=month, day=1, tzinfo=timezone.utc) - timedelta(seconds=1)
    last_time = datetime(year=year, month=(month+1), day=1, tzinfo=timezone.utc)
    events_list = []
    query = """SELECT * FROM events
                WHERE date BETWEEN %s AND %s"""
    tuple = (first_time,last_time)
    results = sql_methods.execute_read_query(query, tuple)
    for event in results:
        events_list.append({'event_id': event[0], 'event_name': event[1], 'date' : event[2], 'location': event[3], 'description': event[4]})
    if events_list:
        return events_list
    else:
        raise HTTPException(status_code=404, detail='No event found.')
