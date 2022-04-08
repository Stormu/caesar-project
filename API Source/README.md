## The Alexandria API
This API utilizes the FastAPI python framework to create a functional API for redacted project. Server is ran with Uvicorn.

# How to run the API locally
Save the files locally to your computer. After that, open command prompt and run this command to start running the API using Uvicorn.

uvicorn main:app --reload

Alternatively, you can also run the included batch file to run the API.

This will enable the API to update in real time as changes are made. The API is hosted locally  at http://127.0.0.1:8000/ . To see the documentation of the API,
go to http://127.0.0.1:8000/docs#/ . To see a different UI of the API documentation, use http://127.0.0.1:8000/redoc .

# Python packages used in the production of this API
FastAPI and Uvicorn are required to run the API.
MySQL connector is required in order to connect the API to a specific database server and to complete query executions.
Python-Multipart is used to allow forms for processing token requests.
passlib is used in the hashing of passwords.
OpenSSL v3 used to create secret keys for access tokens and refresh tokens. (https://slproweb.com/products/Win32OpenSSL.html ).
python-jose[cryptography] used for creating an access token and validating an active access token.

# Included Files
Included files are source code for the API.

.env - file including security varaiables related to connecting to a MySQL database and secret keys
dependencies.py - includes code primarily related to authentication for the API.
main.py - main driver for the API.
Folders:
custom_packages - you'll find a python file tailored towards handling MySQL queries found around the source code of the API.
routers - contains FastAPI router files meant to keep the API mostly modular. Included are two other folders full of router files dedicated to user management and book management.
variables - this folder containers a file that the API opens to get it's description from.
