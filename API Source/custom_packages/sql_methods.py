import mysql.connector
from mysql.connector import Error

import os
from dotenv import load_dotenv

load_dotenv()

host_name = os.getenv('HOST_NAME')
user_name = os.getenv('USER_NAME')
user_password = os.getenv('USER_PASSWORD')
user_db = os.getenv('USER_DB')

connection = None

def create_connection():
    cnx = None
    try:
        cnx = mysql.connector.connect(
            host=host_name,
            user=user_name,
            passwd=user_password,
            database=user_db
        )
    except Exception as e:
        pass
    
    return cnx
    

def execute_query(query, data):
    global connection 
    try:
        cursor = connection.cursor()
        try:
            cursor.execute(query, data)
            connection.commit()
            return "Success"
        except Exception as e:
            return e
    except Exception as e:
        try:
            connection = create_connection()
            cursor = connection.cursor()
            cursor.execute(query, data)
            connection.commit()
            return "Success"
        except Exception as e:
            return e
         
def execute_transaction(queries, datasets):
    global connection 
    try:
        cursor = connection.cursor()
        try:
            for i in range(len(queries)):
                cursor.execute(queries[i], datasets[i])
            connection.commit()
            return "Success"
        except Exception as e:
            return e
    except Exception as e:
        try:
            connection = create_connection()
            cursor = connection.cursor()
            for i in range(len(queries)):
                cursor.execute(queries[i], datasets[i])
            connection.commit()
            return "Success"
        except Exception as e:
            return e

def execute_read_query(query, data):
    global connection
    try:
        cursor = connection.cursor()
        cursor.execute(query, data)
        result = cursor.fetchall()
        return result
    except Exception as e:
        connection = create_connection()
        cursor = connection.cursor()
        cursor.execute(query, data)
        result = cursor.fetchall()
        return result
