#!/usr/bin/python3

# Script per l'importazione delle taglie
# delle uniformi AGESCI su DB per Aggregatore Ordini AGESCI

import sqlite3
import sys

def printquery(name, price, size):
    query = "INSERT INTO " + db_table + " ( "
    query += db_name_field + " , "
    query += db_size_field + " , "
    query += db_price_field + " ) "
    query += " VALUES ('"
    query += name + "','"
    query += size + "','"
    query += price.replace(",", ".") + "');"
    print(query)
    return query

def execquery(cursor, query):
    cursor.execute(query)

def cleartable(cursor):
    execquery(cursor, "DELETE FROM " + db_table + ";")
    execquery(cursor, "VACUUM;")

db_table = "prezzitaglie"
db_name_field = "nomeOggetto"
db_size_field = "taglia"
db_price_field = "prezzo"

if (len(sys.argv) != 3):
    print("Usage: ./parser.py SOURCE DATABASE")
    exit(1)

print("Uniformi agesci parser")
print("SOURCE FILE: " + sys.argv[1])
print("DATABASE FILE: " + sys.argv[2])

target = open(sys.argv[1], 'r')
conn = sqlite3.connect(sys.argv[2])
c = conn.cursor()

cleartable(c)

for line in target:

    if line == "\n":
        continue

    tokens = line.split()

    foundtg = False
    send_name = ""
    send_price = tokens[-1]

    for tk in tokens:
        if (tk == "tg."):
            foundtg = True
            continue
        if (foundtg == False):
            send_name += tk + " "
            continue
        if (foundtg == True):
            if ("-" not in tk):
                # Taglia unica
                send_taglia = tk
                execquery(c, printquery(send_name[:-1], send_price, tk))
            elif (tk.count("-") > 1):
                # Range semplice
                for taglies in tk.split("-"):
                    execquery(c, printquery(send_name[:-1], send_price, taglies))
            elif (tk.count("-") == 1):
                # Range da interpolare
                start = int(tk.split("-")[0])
                stop = int(tk.split("-")[1])
                step = 0
                if (int(start) % 2 == 0):
                    step = 2
                else :
                    step = 1
                while (start <= stop):
                    execquery(c, printquery(send_name[:-1], send_price, str(start)))
                    start += step
            break



