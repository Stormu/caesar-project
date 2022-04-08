from typing import Optional, List
from fastapi import APIRouter, Depends, HTTPException
from dependencies import authenticate_admin_privileges

from custom_packages import sql_methods
from datetime import datetime
from pydantic import BaseModel

router = APIRouter(prefix = "/books", tags = ["Books"])

#Book information without inventory statistics
class Book(BaseModel):
    title: str
    author: str
    publisher: str
    ISBN: str
    blurb: str
    
#Book information including inventory statistics
class BookInv(Book):
    stock: int
    checkedOut: int
 
"""
get_book_from_db: takes information of book from database
Requests: title or ISBN

returns dictionary of data 
""" 
async def get_book_from_db(title: Optional[str] = None, ISBN: Optional[str] = None):
    if title:
        query = """SELECT * FROM books
                    WHERE title = %s"""
        tuple = (title,)
    elif ISBN:
        query = """SELECT * FROM books
                    WHERE ISBN = %s"""
        tuple = (ISBN,)
    else:
        raise HTTPException(status_code=400, detail='Missing title or ISBN of book.')
    results = sql_methods.execute_read_query(query, tuple)
    if results:
        book = results[0]
        return {'title': book[0], 'author': book[1], 'publisher' : book[2], 'ISBN': book[3],'blurb': book[4]}
    else:
        raise HTTPException(status_code=404, detail='Book not found.')

"""
get_inventory_of_book: gets inventory statistics of books
Requests: ISBN

returns dictionary including stock and number checked out books
"""       
async def get_inventory_of_book(ISBN: str):
    query = """SELECT * FROM inventory
                WHERE ISBN = %s"""
    tuple = (ISBN,)
    results = sql_methods.execute_read_query(query, tuple)
    return {'stock': results[0][1], 'checkedOut': results[0][2]}

"""
add_book: adds a book to the database
Requests: title, ISBN, token
Optional requests: author, publisher, blurb

returns title of book and status
"""
@router.post("/")
async def add_book(title: str, ISBN: str, authorized: bool = Depends(authenticate_admin_privileges), author: Optional[str] = None, publisher: Optional[str] = None, blurb: Optional[str] = None):
    if authorized:
        query = """INSERT INTO books
                   (title, author, publisher, ISBN, blurb) 
                   VALUES (%s, %s, %s, %s, %s)"""
        tuple = (title, author, publisher, ISBN, blurb)
        status = sql_methods.execute_query(query, tuple)
        
        if status == "Success":
            query = """INSERT INTO inventory
                   (ISBN, stock, checkedOut) 
                   VALUES (%s, 1, 0)"""
            tuple = (ISBN,)
            status = sql_methods.execute_query(query, tuple)
    else:
        raise HTTPException(status_code=401, detail='Unauthorized.')
    return {'title': title,'status': status}
    
"""
search_book: searchs for books matching input search
Requests: title search

returns list of books having similarities to the search
"""
@router.get("/search", response_model=List[Book])
async def search_book(title: str):
    data = []
    query = """SELECT * FROM books
                WHERE title LIKE %s"""
    tuple = ('%'+title+'%',)
    results = sql_methods.execute_read_query(query, tuple)
    for books in results:
        data.append({'title': books[0], 'author': books[1], 'publisher' : books[2], 'ISBN': books[3],'blurb': books[4]})
    if data:
        return data
    else:
        raise HTTPException(status_code=404, detail='No books founds.')
   
"""
get_book: fetches information of a book from the database
Requests: title or ISBN

returns dictionary of information of the book and inventory
"""
@router.get("/", response_model=BookInv)
async def get_book(title: Optional[str] = None, ISBN: Optional[str] = None):
    if title:
        results = await get_book_from_db(title=title)
    else:
        results = await get_book_from_db(ISBN=ISBN)
    inventoryStats = await get_inventory_of_book(results['ISBN'])
    data = {**results, **inventoryStats}
    return data
    
"""
get_book: fetches information of a book from the database
Requests: title or ISBN

returns dictionary of information of the book and inventory
"""
@router.delete("/")
async def remove_book(authorized: bool = Depends(authenticate_admin_privileges), title: Optional[str] = None, ISBN: Optional[str] = None):
    if title:
        data = await get_book_from_db(title)
    elif ISBN:
        data = await get_book_from_db(ISBN)
    else:
        raise HTTPException(status_code=400, detail='Missing title or ISBN of book.')
    if data:
        query = "DELETE FROM books WHERE ISBN = %s"
        tuple = (data['ISBN'],)
        status = sql_methods.execute_query(query, tuple)
        query = "DELETE FROM inventory WHERE ISBN = %s"
        tuple = (data['ISBN'],)
        status = sql_methods.execute_query(query, tuple)
        return {'title': data['title'], 'status': status}
    else:
        raise HTTPException(status_code=404, detail='Book not found.')