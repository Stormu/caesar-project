from typing import Optional
from datetime import timedelta

from fastapi import FastAPI, Depends
from fastapi.middleware.cors import CORSMiddleware
from fastapi.security import OAuth2PasswordRequestForm
from routers import forum, events, token #books, 
from routers.users import general, password, privacy, profile_images, usernames, users
from routers.books import addbook, getbooks, editbook
from pydantic import BaseModel

with open("./variables/app_description", 'r') as handler:
    desc = handler.read()

app = FastAPI(
    title="TheAlexandria API",
    description=desc,
    version="0.9.2",
)

class API_Info(BaseModel):
    api: str
    version: str
    api_url: str
    documentation: str
    
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(token.router)
#app.include_router(books.router)
app.include_router(addbook.router)
app.include_router(getbooks.router)
app.include_router(editbook.router)
app.include_router(events.router)
app.include_router(forum.router)
app.include_router(users.router)
app.include_router(general.router)
app.include_router(password.router)
app.include_router(privacy.router)
app.include_router(profile_images.router)
app.include_router(usernames.router)

@app.get("/", response_model=API_Info, tags=["Root"])
def get_api():
    return {"api": "TheAlexandria", 
            "version": "0.9.2", 
            "api_url": "https://thealexandriasys.com/api/", 
            "documentation": "https://thealexandriasys.com/api/docs"}
