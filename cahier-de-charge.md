Plateforme complète de réservation de billets de train SETRAG & gestion d’expédition de colis

Vous êtes une équipe chargée de concevoir et livrer une plateforme de réservation de billets de train et de gestion d’expédition de colis/fret pour SETRAG, basée sur une architecture microservices avec Docker, Kubernetes, et MySQL comme base de données principale.

Technologies principales

Backend : Laravel (PHP)

Frontend : React (TypeScript)

Agent IA : Python (FastAPI)

Base de données : MySQL

Architecture : microservices

Conteneurisation : Docker

Orchestration : Kubernetes

Suivi en temps réel : WebSocket/MQTT + cartographie

Objectifs

Réservation en ligne des billets voyageurs avec choix de siège, paiement, annulation, échange.

Gestion des expéditions (colis express, Colirail, fret complet) avec suivi temps réel.

Suivi GPS en direct des trains (voyageurs et fret) et mise à jour en temps réel des réservations.

Agent IA multilingue pour répondre aux questions clients, assister aux réservations et au suivi des colis.

Portails B2C, B2B et back-office pour agents et opérateurs.

Architecture microservices

API Gateway : authentification, routage, quotas.

Service Auth & Identity (Laravel + MySQL) : comptes, rôles, permissions, OAuth2/JWT.

Service Horaires & Inventaire (Laravel + MySQL) : gares, trajets, sièges, disponibilités.

Service Tarification & Réservation (Laravel + MySQL) : tarifs, réservations, PNR, paiement.

Service Paiement (Laravel) : intégrations PSP, gestion transactions.

Service Expédition (Laravel + MySQL) : création, gestion et suivi colis.

Service Tracking (Python/FastAPI + MySQL) : ingestion GPS, calcul ETA, diffusion temps réel.

Service Notifications (Laravel) : envoi d’e-mails, SMS, push.

Service IA (Python/FastAPI) : NLU, RAG, intégrations API internes.

Service Analytics (Python + MySQL) : reporting et tableaux de bord.

Suivi temps réel

Ingestion GPS via MQTT/HTTP.

Stockage optimisé séries temporelles dans MySQL.

Diffusion WebSocket vers frontend pour positions trains et statut colis.

Frontend React

React + TypeScript, PWA, notifications push.

Cartographie (Leaflet ou MapLibre) pour suivi trains/colis.

State management (Zustand ou Redux Toolkit).

UI Components (shadcn/ui ou MUI).

Observabilité

Logs : EFK (Elasticsearch, Fluent Bit, Kibana)

Monitoring : Prometheus + Grafana

Traces : OpenTelemetry + Jaeger

Déploiement

Docker pour chaque service.

Kubernetes avec Helm Charts.

CI/CD via GitHub Actions/GitLab CI + ArgoCD.

Sécurité

TLS, OAuth2/JWT, chiffrement des données sensibles en MySQL.

RBAC strict et journaux d’audit.

Livrables

Diagrammes d’architecture et séquences.

Code Laravel, React et Python.

Manifests Kubernetes et Dockerfiles.

Documentation API.

Jeux de tests unitaires et E2E.