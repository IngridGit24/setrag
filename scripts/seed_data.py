#!/usr/bin/env python3
"""
Script pour initialiser les données de test pour SETRAG
"""

import requests
import json
import time
import random
from datetime import datetime, timedelta

# Configuration des services (ports corrigés selon start.sh)
SERVICES = {
    'users': 'http://localhost:8104',
    'inventory': 'http://localhost:8105',
    'pricing': 'http://localhost:8106',
    'tracking': 'http://localhost:8001'
}

# Stations SETRAG au Gabon (plus complètes)
STATIONS = [
    {"name": "Libreville", "latitude": 0.4162, "longitude": 9.4673},
    {"name": "Owendo", "latitude": 0.3000, "longitude": 9.5000},
    {"name": "Ndjolé", "latitude": -0.1833, "longitude": 10.6833},
    {"name": "Boumango", "latitude": -1.5000, "longitude": 11.8333},
    {"name": "Lastoursville", "latitude": -0.8167, "longitude": 12.7000},
    {"name": "Moanda", "latitude": -1.5667, "longitude": 13.2000},
    {"name": "Franceville", "latitude": -1.6333, "longitude": 13.5833}
]

# Classes de voyage et prix de base
TRAVEL_CLASSES = {
    "Economy": 15000,
    "Business": 30000,
    "VIP": 50000
}

def create_test_user(service_url, user_data):
    """Créer un utilisateur de test"""
    try:
        # Corriger le format pour l'API users
        corrected_data = {
            "email": user_data["email"],
            "full_name": user_data["name"],  # API attend full_name, pas name
            "password": user_data["password"]
        }
        
        response = requests.post(f"{service_url}/users", json=corrected_data)
        if response.status_code in [200, 201]:  # Accepter 200 OK et 201 Created
            print(f"✅ Utilisateur créé: {user_data['email']}")
            return response.json()
        else:
            print(f"❌ Erreur création utilisateur {user_data['email']}: {response.text}")
            return None
    except Exception as e:
        print(f"❌ Erreur connexion service users: {e}")
        return None

def create_test_station(service_url, station_data):
    """Créer une station de test"""
    try:
        # Corriger le format pour l'API inventory
        corrected_data = {
            "name": station_data["name"],
            "latitude": station_data.get("latitude", 0.0),  # Ajouter latitude
            "longitude": station_data.get("longitude", 0.0)  # Ajouter longitude
        }
        
        response = requests.post(f"{service_url}/stations", json=corrected_data)
        if response.status_code in [200, 201]:  # Accepter 200 OK et 201 Created
            print(f"✅ Station créée: {station_data['name']}")
            return response.json()
        else:
            print(f"❌ Erreur création station {station_data['name']}: {response.text}")
            return None
    except Exception as e:
        print(f"❌ Erreur connexion service inventory: {e}")
        return None

def create_test_trip(service_url, trip_data, stations_map):
    """Créer un voyage de test"""
    try:
        # Corriger le format pour l'API inventory
        origin_id = stations_map.get(trip_data["origin_station"])
        dest_id = stations_map.get(trip_data["destination_station"])
        
        if not origin_id or not dest_id:
            print(f"❌ Stations non trouvées pour le voyage: {trip_data['origin_station']} → {trip_data['destination_station']}")
            return None
            
        corrected_data = {
            "origin_station_id": origin_id,
            "destination_station_id": dest_id,
            "departure_time": trip_data["departure_time"],
            "arrival_time": trip_data["arrival_time"]
        }
        
        response = requests.post(f"{service_url}/trips", json=corrected_data)
        if response.status_code in [200, 201]:  # Accepter 200 OK et 201 Created
            print(f"✅ Voyage créé: {trip_data['origin_station']} → {trip_data['destination_station']}")
            return response.json()
        else:
            print(f"❌ Erreur création voyage: {response.text}")
            return None
    except Exception as e:
        print(f"❌ Erreur connexion service inventory: {e}")
        return None

def seed_seats(service_url, trip_id, num_seats=50):
    """Initialiser les sièges pour un voyage"""
    try:
        response = requests.post(f"{service_url}/trips/{trip_id}/seats/seed", json={"num_seats": num_seats})
        if response.status_code in [200, 201]:  # Accepter 200 OK et 201 Created
            print(f"✅ {num_seats} sièges initialisés pour le voyage {trip_id}")
            return True
        else:
            print(f"❌ Erreur initialisation sièges: {response.text}")
            return False
    except Exception as e:
        print(f"❌ Erreur connexion service inventory: {e}")
        return False

def create_test_booking(service_url, booking_data, token=None):
    """Créer une réservation de test"""
    try:
        headers = {
            'Content-Type': 'application/json'
        }
        if token:
            headers['Authorization'] = f'Bearer {token}'
            
        response = requests.post(f"{service_url}/booking", json=booking_data, headers=headers)
        if response.status_code in [200, 201]:  # Accepter 200 OK et 201 Created
            print(f"✅ Réservation créée: {booking_data['passenger_name']} - {booking_data['trip_id']}")
            return response.json()
        else:
            print(f"❌ Erreur création réservation: {response.text}")
            return None
    except Exception as e:
        print(f"❌ Erreur connexion service pricing: {e}")
        return None

def create_test_position(service_url, position_data):
    """Créer une position de train de test"""
    try:
        response = requests.post(f"{service_url}/position", json=position_data)
        if response.status_code in [200, 201]:  # Accepter 200 OK et 201 Created
            print(f"✅ Position créée pour le train {position_data['train_id']}")
            return True
        else:
            print(f"❌ Erreur création position: {response.text}")
            return False
    except Exception as e:
        print(f"❌ Erreur connexion service tracking: {e}")
        return False

def generate_sample_tickets(trips, stations_map, num_tickets=10):
    """Générer des données de tickets de test"""
    tickets = []
    
    for i in range(num_tickets):
        # Choisir un voyage aléatoire
        trip = random.choice(trips)
        
        # Générer des données de passager
        passenger_names = [
            "Jean Dupont", "Marie Martin", "Pierre Durand", "Sophie Leroy",
            "Michel Moreau", "Isabelle Simon", "François Michel", "Catherine Roux",
            "Philippe David", "Nathalie Bertin", "Laurent Rousseau", "Monique Vincent",
            "Gérard Moulin", "Christine Fournier", "André Mercier", "Sylvie Faure"
        ]
        
        ticket_data = {
            "trip_id": trip['id'],
            "seat_no": f"{random.randint(1, 50)}",
            "passenger_name": random.choice(passenger_names),
            "passenger_email": f"passenger{i+1}@example.com"
        }
        
        tickets.append(ticket_data)
    
    return tickets

def main():
    print("🚂 Initialisation des données de test SETRAG")
    print("=" * 50)
    
    # Attendre que les services soient prêts
    print("⏳ Attente du démarrage des services...")
    time.sleep(3)
    
    # 1. Créer des utilisateurs de test
    print("\n👥 Création des utilisateurs de test...")
    test_users = [
        {
            "email": "admin@setrag.ga",
            "name": "Admin SETRAG",
            "password": "admin123"
        },
        {
            "email": "john.doe@example.com",
            "name": "John Doe",
            "password": "password123"
        },
        {
            "email": "marie.dupont@example.com",
            "name": "Marie Dupont",
            "password": "password123"
        },
        {
            "email": "pierre.martin@example.com",
            "name": "Pierre Martin",
            "password": "password123"
        }
    ]
    
    created_users = []
    for user in test_users:
        result = create_test_user(SERVICES['users'], user)
        if result:
            created_users.append(result)
    
    # 2. Créer des stations de test avec coordonnées GPS
    print("\n🏢 Création des stations de test...")
    created_stations = []
    stations_map = {}  # Pour mapper nom -> id
    
    for station in STATIONS:
        result = create_test_station(SERVICES['inventory'], station)
        if result:
            created_stations.append(result)
            stations_map[station['name']] = result['id']
    
    # 3. Créer des voyages de test
    print("\n🚂 Création des voyages de test...")
    now = datetime.now()
    test_trips = [
        {
            "origin_station": "Libreville",
            "destination_station": "Franceville",
            "departure_time": (now + timedelta(hours=2)).isoformat(),
            "arrival_time": (now + timedelta(hours=8)).isoformat(),
            "price": 25000
        },
        {
            "origin_station": "Franceville",
            "destination_station": "Libreville",
            "departure_time": (now + timedelta(hours=10)).isoformat(),
            "arrival_time": (now + timedelta(hours=16)).isoformat(),
            "price": 25000
        },
        {
            "origin_station": "Libreville",
            "destination_station": "Moanda",
            "departure_time": (now + timedelta(hours=4)).isoformat(),
            "arrival_time": (now + timedelta(hours=6)).isoformat(),
            "price": 15000
        },
        {
            "origin_station": "Moanda",
            "destination_station": "Libreville",
            "departure_time": (now + timedelta(hours=12)).isoformat(),
            "arrival_time": (now + timedelta(hours=14)).isoformat(),
            "price": 15000
        },
        {
            "origin_station": "Libreville",
            "destination_station": "Owendo",
            "departure_time": (now + timedelta(hours=1)).isoformat(),
            "arrival_time": (now + timedelta(hours=1, minutes=30)).isoformat(),
            "price": 5000
        },
        {
            "origin_station": "Owendo",
            "destination_station": "Libreville",
            "departure_time": (now + timedelta(hours=15)).isoformat(),
            "arrival_time": (now + timedelta(hours=15, minutes=30)).isoformat(),
            "price": 5000
        }
    ]
    
    created_trips = []
    for trip in test_trips:
        result = create_test_trip(SERVICES['inventory'], trip, stations_map)
        if result:
            created_trips.append(result)
    
    # 4. Initialiser les sièges pour chaque voyage
    print("\n💺 Initialisation des sièges...")
    for trip in created_trips:
        seed_seats(SERVICES['inventory'], trip['id'], 50)
    
    # 5. Créer des réservations de test
    print("\n🎫 Création des réservations de test...")
    sample_tickets = generate_sample_tickets(created_trips, stations_map, 15)
    
    # Se connecter avec un utilisateur pour créer des réservations
    if created_users:
        try:
            login_response = requests.post(f"{SERVICES['users']}/oauth/token", 
                headers={'Content-Type': 'application/x-www-form-urlencoded'},
                data={
                    'username': 'admin@setrag.ga',
                    'password': 'admin123',
                    'grant_type': 'password'
                })
            
            if login_response.status_code == 200:
                token = login_response.json()['access_token']
                print(f"✅ Connexion réussie pour créer des réservations")
                
                created_bookings = 0
                for ticket in sample_tickets:
                    result = create_test_booking(SERVICES['pricing'], ticket, token)
                    if result:
                        created_bookings += 1
                
                print(f"✅ {created_bookings} réservations créées avec succès")
            else:
                print("❌ Impossible de se connecter pour créer des réservations")
        except Exception as e:
            print(f"❌ Erreur lors de la création des réservations: {e}")
    
    # 6. Créer des positions de trains de test
    print("\n📍 Création des positions de trains...")
    test_positions = [
        {
            "train_id": "TRAIN001",
            "latitude": 0.4162,
            "longitude": 9.4673,
            "speed": 80,
            "direction": 45,
            "timestamp": datetime.now().isoformat()
        },
        {
            "train_id": "TRAIN002",
            "latitude": -1.6333,
            "longitude": 13.5833,
            "speed": 75,
            "direction": 225,
            "timestamp": datetime.now().isoformat()
        },
        {
            "train_id": "TRAIN003",
            "latitude": -1.5667,
            "longitude": 13.2000,
            "speed": 90,
            "direction": 90,
            "timestamp": datetime.now().isoformat()
        }
    ]
    
    for position in test_positions:
        create_test_position(SERVICES['tracking'], position)
    
    print("\n" + "=" * 50)
    print("✅ Initialisation terminée !")
    print(f"📊 Résumé:")
    print(f"   - {len(created_users)} utilisateurs créés")
    print(f"   - {len(created_stations)} stations créées")
    print(f"   - {len(created_trips)} voyages créés")
    print(f"   - {len(test_positions)} positions de trains créées")
    print(f"   - {len(sample_tickets)} réservations générées")
    print("\n🎯 Vous pouvez maintenant tester l'application complète !")

if __name__ == "__main__":
    main()
