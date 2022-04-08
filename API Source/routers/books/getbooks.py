from typing import Optional, List
from fastapi import APIRouter, Depends, HTTPException

from custom_packages import sql_methods
from datetime import datetime
from pydantic import BaseModel

router = APIRouter(prefix = "/books", tags = ["Books"])

#Book information without inventory statistics
class Book(BaseModel):
    book_id: int
    title: str
    authors: Optional[str] = None
    description: str
    edition: Optional[str] = None
    publisher: str
    ISBN13: str
    ISBN10: Optional[str] = None
    ISSN: Optional[str] = None
    
"""
search_book: searchs for books matching input search
Requests: title search

returns list of books having similarities to the search
"""
@router.get("/search", response_model=List[Book])
async def search_book(title: str):
    data = []
    query = """SELECT * FROM booksinfo
                WHERE title LIKE %s"""
    tuple = ('%'+title+'%',)
    results = sql_methods.execute_read_query(query, tuple)
    for books in results:
        data.append({'book_id': books[0],
                     'title': books[1],
                     'authors' : books[2],
                     'description': books[3],
                     'edition': books[4],
                     'publisher': books[5],
                     'ISBN13': books[6],
                     'ISBN10': books[7],
                     'ISSN': books[8]})
    if data:
        return data
    else:
        raise HTTPException(status_code=404, detail='No books founds.')
        
"""
search_book: searchs for books matching input search
Requests: title search

returns list of books having similarities to the search
"""
@router.get("/", response_model=Book)
async def get_book(book_id: int):
    data = []
    query = """SELECT * FROM booksinfo
                WHERE book_id = %s"""
    tuple = (book_id,)
    results = sql_methods.execute_read_query(query, tuple)
    if results:
        book = results[0]
        data ={'book_id': book[0],
                'title': book[1],
                'authors' : book[2],
                'description': book[3],
                'edition': book[4],
                'publisher': book[5],
                'ISBN13': book[6],
                'ISBN10': book[7],
                'ISSN': book[8]}
    if data:
        return data
    else:
        raise HTTPException(status_code=404, detail='No books founds.')