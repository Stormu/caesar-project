from typing import Optional, List
from fastapi import APIRouter, Depends, HTTPException, Query
from dependencies import authenticate_librarian_privileges

from custom_packages import sql_methods
from datetime import datetime
from pydantic import BaseModel

router = APIRouter(prefix = "/books", tags = ["Books"])
 

"""
add_book: adds a book to the database
Requests: title, description, publisher, ISBN13
Optional requests: authors, edition, ISBN10, ISSN

returns status code
"""
@router.post("/", status_code=201)
async def add_book(title: Optional[str] = Query(..., max_length=200),
                   authors: Optional[str] = Query(None, max_length=100),
                   description: Optional[str] = Query(..., max_length=10000),
                   edition: Optional[str] = Query(None, max_length=50),
                   publisher: Optional[str] = Query(..., max_length=200),
                   ISBN13: Optional[str] = Query(..., max_length=25),
                   ISBN10: Optional[str] = Query(None, max_length=25),
                   ISSN: Optional[str] = Query(None, max_length=8),
                   authorized: bool = Depends(authenticate_librarian_privileges)):
    queries = []
    datasets = []
    #Setting up the first query into the booksinfo table
    query1 = """INSERT INTO booksinfo
            (title, authors, description, edition, publisher, ISBN13, ISBN10, ISSN) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s)"""
    data1 = (title, authors, description, edition, publisher, ISBN13, ISBN10, ISSN)
    queries.append(query1)
    datasets.append(data1)
    #Setting up the second query to insert into booksinv
    query2 = """INSERT INTO booksinv
            (title) 
            VALUES (%s)"""
    data2 = (title,)
    queries.append(query2)
    datasets.append(data2)
    status = sql_methods.execute_transaction(queries, datasets)
    if status != "Success":
        raise HTTPException(status_code=400, detail='Improper input.')