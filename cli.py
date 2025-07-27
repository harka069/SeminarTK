import cmd, sys
import getpass
import requests
import json
import urllib3
import questionary
from datetime import datetime
from tabulate import tabulate
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
class avtogvisn(cmd.Cmd):
    intro = 'Welcome\n'
    prompt = '(avtogvisn) '

    def do_login(self, arg):
        'Login user to application'
        username = input("Username: ").strip()
        if not username:
            print("Username cannot be empty.")
            return
        password = getpass.getpass("Password: ")

        try:
            response = requests.post(
                "https://localhost/avtogvisn/api/login.php",
                json={"username": username, "password": password},
                headers={"Content-Type": "application/json"},
                verify=False,            
            )
            if response.status_code == 200:
                self.login =json.loads(response.text)
                print (f"logged in as", self.login["Name"],  self.login["Surname"],  self.login["Email"])
                avtogvisn.prompt = f'{self.login["Name"]}@avtogvisn '
            else:
                #print(f"Error {response.status_code}: {response.text}")
                print("Napacno uporabnisko ime ali geslo!")
        except requests.exceptions.RequestException as e:
            print(f" Request failed: {e}")
    def do_register(self, arg):
        'Register new user'
        username = input("Username: ").strip()
        if not username:
            print("Username cannot be empty.")
            return
        surname = input("Surname: ").strip()
        if not surname:
            print("Username cannot be empty.")
            return
        email = input("email: ").strip()
        if not email:
            print("Username cannot be empty.")
            return
        password = getpass.getpass("Password: ").strip()
        password_check = getpass.getpass("Retype password: ").strip()
        if(password != password_check):
            print ("password doesnt match")  
            return
        try:
            response = requests.post(
                "https://localhost/avtogvisn/register.php",
                json={"name": username,"surname": surname, "email": email, "password": password},
                headers={"Content-Type": "application/json"},
                verify=False,
            
            )

            # Handle response
            if response.status_code == 200:
                print("New user added")
            else:
                print(f"Error {response.status_code}: {response.text}")

        except requests.exceptions.RequestException as e:
            print(f" Request failed: {e}")
            return True
    def do_edit_credentials(self,arg): 
        'Edit user (your) credentials'  
        if (not(is_user_registered(self,arg))):
           print("user is not registered")
           return
        params={}
        print('enter credentials or skip with "enter"')
        temp = input("Username: ").strip()
        if temp:
            params["name"]=temp
            self.login["Name"]=temp
            exit
        temp = input("Surname: ").strip()
        if temp:
            params["surname"]=temp
            exit
        temp = input("Email: ").strip()   
        if temp:
            params["mail"]=temp
            exit
        temp = getpass.getpass("Password: ").strip()
        if temp:
            temp1 = getpass.getpass("Retype password: ").strip()
            if temp == temp1:
                params["password"]=temp
                exit
            else:
                print ("password doesnt match")  
                return
        print("Type your old password for conformation")
        password=getpass.getpass("Retype password: ").strip()
        #if(self.login["Password"]==password):
        params["oldPassword"]=password
        json_data = json.dumps(params)        
        response = fetch_with_auth(self,'http://localhost/avtogvisn/api/users',method="PUT",payload=params)
        if(response.status_code == 200):
            print("Uporabnik uspešno posodobljen!")
        else:
            print("Password is not correct!")
            return
    def do_exit(self, arg):
        print("Goodbye!")
        del self.login
        return True
    def do_about(self,arg):
        'shows about'
        print("""This project was developed as part of the Seminar iz načrtovanja in razvoja programske opreme v telekomunikacijah course.
                 The main motivation behind the project is to assist users in making informed decisions when buying a used car in Slovenia. 
                 All data used is publicly available from the Ministry of Infrastructure (due to new regulations, data from 2022): 
                 https://podatki.gov.si/dataset/rezultati-tehnicnih-pregledov-motornih-vozil \n
                 The application allows users to compare annual technical inspection results across different car brands and production years. 
                 The concept is inspired by the website\n
                 www.autolog.si""")
    def do_car_statistic(self,arg):
        'prompts user to enter car to calculate how many car failed yearly inspection'
        
        
        choice = input("want to use saved querry? Y/N: ").strip().lower()
        if choice == 'y':
            user_queries = fetch_query(self)
            query_number = int(input("Which query You want to use(to see query number please use command my_queries): ").strip())
            query_entry = next((q for q in user_queries if q.get("query_number") == query_number), None)
            params = {
                    "znamka": query_entry["znamka"],
                    "model": query_entry["model"],
                    "fuel": query_entry["fuel"],
                    "start_date": query_entry["start_date"],    
                    "end_date": query_entry["end_date"],
                    "min_km": query_entry["min_km"],
                    "max_km": query_entry["max_km"]
                   }
            results = fetch_with_auth(self,'http://localhost/avtogvisn/api/cars',
                                   method="GET",
                                   params=params)
            #results=car_querry(self,params)[0]
            if not query_entry:
                print("Query number not found.")
                return
        else:           
            params=car_querry(self)
            results = fetch_with_auth(self,'http://localhost/avtogvisn/api/cars',
                                   method="GET",
                                   params=params)
        if results:
            stats_list = results.json()
            stats=stats_list[0]
            if(stats["total_count"])>0:
                print(f"""
                All cars: {stats["total_count"]}
                Passed inspection: {stats["brezhiben_count"]}
                Passed inspection (in %): {round((stats["brezhiben_count"]*100/stats["total_count"]),2)}
                Failed inspection: {stats["ne_brezhiben_count"]}
                """)
            else:
                print("No cars with selected paramateres found")
        else:
            print("No results returned from API.")
        if choice == 'y':
            return
        choice = input("want to save querry? Y/N: ").strip().lower()
        if choice == 'n':
            return
        result = fetch_with_auth(self,"http://localhost/avtogvisn/api/users","POST",payload=params)
        if result.status_code == 200:
            print("Query saved")
            return
        else:
            print("Unexpected API response")
            print(result.text)
            return
    def do_my_queries(self,arg):
        'shows querries from user'
        user_queries = fetch_query(self)
        """response = fetch_with_auth(self,"http://localhost/avtogvisn/api/users/favourite","GET")
        data = response.json()
        for i, entry in enumerate(data, start=1):
            entry["query_number"] = i
        columns = ["query_number", "znamka", "model", "start_date", "end_date", "fuel", "min_km", "max_km"]
        user_queries = data"""
        columns = ["query_number", "znamka", "model", "start_date", "end_date", "fuel", "min_km", "max_km"]
        rows = [[d[col] for col in columns] for d in user_queries]
        print(tabulate(rows, headers=columns, tablefmt="fancy_grid"))   
    def do_delete_querry(self,arg):
        'delete querry'
        user_queries = fetch_query(self)
        query_number = int(input("Delete query number (to see query number please use command my_queries): ").strip())
        match = next((q for q in user_queries if q["query_number"] == query_number), None)
        if not match:
            print("No query found with that number.")
            return
        query_id = match["query_id"]
        result = fetch_with_auth(self,"http://localhost/avtogvisn/api/users/favourite","DELETE",params={"QueryID": query_id})
        if result.status_code == 200:
            print("Query deleted succesfully")
        else:  
            print("Unexpected API response")
            print(result.text)
    def do_car_yearly_statistic(self,arg):
        'returns car annual tehnical inspection by year'
        params=car_yearly_querry(self)
        results = fetch_with_auth(self,'http://localhost/avtogvisn/api/cars',
                                method="GET",
                                params=params)
        if results:
            stats_list = results.json()
            #print(stats_list)
            rows = []           
            for item in stats_list:
                total = item['total_count']
                brezhiben = item['brezhiben_count']
                percent = round((brezhiben / total) * 100, 2) if total > 0 else 0.0
                rows.append([
                    item['year'],
                    total,
                    brezhiben,
                    item['ne_brezhiben_count'],
                    f"{percent} %"
                ])
            headers = ['Year', 'Total', 'Passed', 'Failed', 'Percent Passed']
            print(tabulate(rows, headers=headers, tablefmt="fancy_grid"))
        else:
            print("No results returned from API.")

#HELPER functions
def is_user_registered(self,arg):
    if hasattr(self, 'login'):
        return True
    else:
        return False  
    
def fetch_with_auth(self, url, method="GET", params=None, payload=None):
    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {self.login['access_token']}"
    }

    try:
        response = requests.request(
            method=method.upper(),
            url=url,
            headers=headers,
            params=params,
            json=payload,
            verify=False  
        )

        if response.status_code == 200:
            return response
        elif response.status_code == 401:
            headers = {"Content-Type": "application/json"}
            response = requests.request(
                'http://localhost/php_auth_jwt_tut/api/refresh.php',
                method="POST",
                headers=headers,
                params=self.login["refresh_token"],
                verify=False
            )
            if response.status_code == 200:
                self.login =json.loads(response.text)
            else:
                print("Exit aplication and login again!")
        else:
            print(f"Error {response.status_code}: {response.text}")
        return response.status_code
    except requests.exceptions.RequestException as e:
        print(f"Request failed: {e}")
        return None

def is_valid_kilometers(value):
    return value.isdigit()

def is_valid_date(value):
    try:
        datetime.strptime(value, "%Y-%m-%d")
        return True
    except ValueError:
        return False
    
def car_querry(self,params=None):
        if params == None:
            response = fetch_with_auth(self,'http://localhost/avtogvisn/api/cars',method="GET")
            brands = response.json()
            if not isinstance(brands, list):
                print("Unexpected API response")
            else:
                selected_brand = questionary.autocomplete(
                    "Car brand:",
                    choices=brands,
                    match_middle=False
                ).ask()
            #fetching&selecting models
            response = fetch_with_auth(self,'http://localhost/avtogvisn/api/cars',
                                    method="GET",
                                    params={"znamka": selected_brand})
            models = response.json()
            if not isinstance(models, list):
                print("Unexpected API response")
            else:
                selected_model= questionary.autocomplete(
                    "Car model:",
                    choices=models,
                    match_middle=False
                ).ask()
            fuel_table= {
                "Petrol": "P",
                "Diesel": "D",
                "LPG":  "LPG",
                "Electric": "-"
            }
            fuel_name= questionary.select(
                "Fuel type:",
                choices=list(fuel_table.keys())
            ).ask()    
            fuel = fuel_table[fuel_name]
            while True:
                km_from = input("Kilometers from: ").strip()
                if is_valid_kilometers(km_from):
                    break
                print("Please enter a valid number.")
            while True:
                km_to = input("Kilometers to: ").strip()
                if is_valid_kilometers(km_to):
                    break
                print("Please enter a valid number.")
            while True:
                date_from = input("Date from (YYYY-MM-DD): ").strip()
                if is_valid_date(date_from):
                    break
                print("Please enter a date in YYYY-MM-DD format.")

            while True:
                date_to = input("Date to (YYYY-MM-DD): ").strip()
                if is_valid_date(date_to):
                    break
                print("Please enter a date in YYYY-MM-DD format.")
            params = {
                "znamka": selected_brand,
                "model": selected_model,
                "fuel": fuel,          
                "start_date": date_from,
                "end_date": date_to,
                "min_km": km_from,
                "max_km": km_to                 
            }
        return params

def car_yearly_querry(self,params=None):
        if params == None:
            response = fetch_with_auth(self,'http://localhost/avtogvisn/api/cars',method="GET")
            brands = response.json()
            if not isinstance(brands, list):
                print("Unexpected API response")
            else:
                selected_brand = questionary.autocomplete(
                    "Car brand:",
                    choices=brands,
                    match_middle=False 
                ).ask()
            #fetching&selecting models
            response = fetch_with_auth(self,'http://localhost/avtogvisn/api/cars',
                                    method="GET",
                                    params={"znamka": selected_brand})
            models = response.json()
            if not isinstance(models, list):
                print("Unexpected API response")
            else:
                selected_model= questionary.autocomplete(
                    "Car model:",
                    choices=models,
                    match_middle=False 
                ).ask()
            fuel_table= {
                "Petrol": "P",
                "Diesel": "D",
                "LPG":  "LPG",
                "Electric": "-"
            }
            fuel_name= questionary.select(
                "Fuel type:",
                choices=list(fuel_table.keys())
            ).ask()    
            fuel = fuel_table[fuel_name]
            date_from = input("Date from (YYYY): ").strip()

            date_to = input("Date to (YYYY): ").strip()
            
            params = {
                "znamka": selected_brand,
                "model": selected_model,
                "fuel": fuel,          
                "start_date": date_from,
                "end_date": date_to,
            }
        return params

def fetch_query(self):
    response = fetch_with_auth(self,"http://localhost/avtogvisn/api/users/favourite","GET")
    user_queries = response.json()
    for i, entry in enumerate(user_queries, start=1):
        entry["query_number"] = i
    return user_queries
if __name__ == '__main__':
    avtogvisn().cmdloop()