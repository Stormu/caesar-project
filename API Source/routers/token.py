from typing import Optional
from datetime import timedelta
from fastapi import APIRouter, Depends, BackgroundTasks
from fastapi.security import OAuth2PasswordRequestForm
from dependencies import (UserBasic, 
                          Token,
                          authenticate_user, 
                          create_access_token, 
                          create_refresh_token,
                          oauth2_scheme,
                          refresh_token_regeneration,
                          refresh_token_blacklist)

router = APIRouter(prefix = "/token", tags = ["Authentication"])

access_token_expiration = 60

"""
login: grants token if credentials are valid
Requests: user+password form data

Returns access token
"""
@router.post("/", response_model=Token)
async def login(form_data: OAuth2PasswordRequestForm = Depends()):
    user_data = await authenticate_user(form_data.username, form_data.password)
    access_token = create_access_token(data={"sub": str(user_data.UID)}, expiration_time=timedelta(minutes=access_token_expiration))
    refresh_token = create_refresh_token(data={"sub": str(user_data.UID)})
    return {'access_token' : access_token, 'refresh_token' : refresh_token, 'token_type' : "bearer"}
    
"""
login: grants token if credentials are valid
Requests: user+password form data

Returns access token
"""
@router.post("/refresh", response_model=Token)
async def refresh(refresh_token: str = Depends(oauth2_scheme)):
    access_token = refresh_token_regeneration(refresh_token, expiration_time=timedelta(minutes=access_token_expiration))
    return {'access_token' : access_token, 'refresh_token' : refresh_token, 'token_type' : "bearer"}
    
"""
login: grants token if credentials are valid
Requests: user+password form data

Returns access token
"""
@router.post("/blacklist", status_code=204)
async def blacklist(refresh_token: str = Depends(oauth2_scheme)):
    await refresh_token_blacklist(refresh_token)