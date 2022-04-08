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
@router.put("/", status_code=204)
async def edit_book(book_id: int,
                   title: Optional[str] = Query(..., max_length=200),
                   authors: Optional[str] = Query(None, max_length=100),
                   description: Optional[str] = Query(..., max_length=10000),
                   edition: Optional[str] = Query(None, max_length=50),
                   publisher: Optional[str] = Query(..., max_length=200),
                   ISBN13: Optional[str] = Query(..., max_length=25),
                   ISBN10: Optional[str] = Query(None, max_length=25),
                   ISSN: Optional[str] = Query(None, max_length=8),
                   authorized: bool = Depends(authenticate_librarian_privileges)):
    #Setting up the query to update booksinfo
    query = """UPDATE booksinfo
             SET title = %s,
             authors = %s, 
             description = %s,
             edition = %s, 
             publisher = %s, 
             ISBN13 = %s, 
             ISBN10 = %s, 
             ISSN = %s
             WHERE book_id = %s"""
    tuple = (title, authors, description, edition, publisher, ISBN13, ISBN10, ISSN, book_id)
    status = sql_methods.execute_query(query, tuple)
    if status != "Success":
        raise HTTPException(status_code=400, detail='Improper input.')