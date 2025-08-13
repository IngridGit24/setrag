import React, { useState } from 'react'
import { BrowserRouter, Link, Route, Routes } from 'react-router-dom'
import Home from './Home'
import Book from './Book'
import Track from './Track'
import AuthPage from './AuthPage'
import Shipping from './Shipping'
import Payment from './Payment'
import Success from './Success'
import GoToTop from '../components/GoToTop'

export default function App() {
  const [isMenuOpen, setIsMenuOpen] = useState(false)
  
  return (
    <BrowserRouter>
      <div className="min-h-screen bg-gray-50 flex flex-col">
        {/* Navigation */}
        <nav className="bg-setrag-primary shadow-lg">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex justify-between h-16">
              <div className="flex items-center">
                <Link to="/" className="flex items-center space-x-2">
                  <span className="text-2xl">🚂</span>
                  <span className="text-white font-bold text-xl">SETRAG</span>
                </Link>
              </div>
              
              {/* Desktop Navigation */}
              <div className="hidden md:flex items-center space-x-4">
                <Link to="/" className="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                  Accueil
                </Link>
                <Link to="/book" className="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                  Réserver
                </Link>
                <Link to="/track" className="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                  Suivi
                </Link>
                <Link to="/shipping" className="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                  Colis
                </Link>
                <Link to="/auth" className="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                  Connexion
                </Link>
              </div>
              
              {/* Mobile menu button */}
              <div className="md:hidden flex items-center">
                <button
                  onClick={() => setIsMenuOpen(!isMenuOpen)}
                  className="text-white hover:text-gray-200 focus:outline-none focus:text-gray-200"
                >
                  <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          {/* Mobile Navigation */}
          {isMenuOpen && (
            <div className="md:hidden">
              <div className="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-setrag-primary-dark">
                <Link to="/" className="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                  Accueil
                </Link>
                <Link to="/book" className="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                  Réserver
                </Link>
                <Link to="/track" className="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                  Suivi
                </Link>
                <Link to="/shipping" className="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                  Colis
                </Link>
                <Link to="/auth" className="text-white block px-3 py-2 rounded-md text-base font-medium hover:text-gray-200">
                  Connexion
                </Link>
              </div>
            </div>
          )}
        </nav>
        
        {/* Main Content */}
        <main className="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/book" element={<Book />} />
            <Route path="/track" element={<Track />} />
            <Route path="/auth" element={<AuthPage />} />
            <Route path="/shipping" element={<Shipping />} />
            <Route path="/payment" element={<Payment />} />
            <Route path="/success" element={<Success />} />
          </Routes>
        </main>
        
        {/* Footer */}
        <footer className="bg-setrag-primary text-white mt-auto">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              <div>
                <div className="flex items-center space-x-2 mb-4">
                  <span className="text-2xl">🚂</span>
                  <span className="font-bold text-xl">SETRAG</span>
                </div>
                <p className="text-gray-200">
                  Votre partenaire de confiance pour le transport ferroviaire au Gabon.
                </p>
              </div>
              
              <div>
                <h3 className="font-semibold text-lg mb-4">Services</h3>
                <ul className="space-y-2">
                  <li><Link to="/book" className="text-gray-200 hover:text-white transition-colors">Réservation billets</Link></li>
                  <li><Link to="/track" className="text-gray-200 hover:text-white transition-colors">Suivi trains</Link></li>
                  <li><Link to="/shipping" className="text-gray-200 hover:text-white transition-colors">Expédition colis</Link></li>
                </ul>
              </div>
              
              <div>
                <h3 className="font-semibold text-lg mb-4">Contact</h3>
                <div className="space-y-2 text-gray-200">
                  <p>📧 contact@setrag.ga</p>
                  <p>📞 +241 01 76 00 00</p>
                  <p>📍 Libreville, Gabon</p>
                </div>
              </div>
            </div>
            
            <div className="border-t border-gray-600 mt-8 pt-8 text-center text-gray-200">
              <p>This app has been build with love by Ingrid</p>
            </div>
          </div>
        </footer>
        
        {/* Go to Top Button */}
        <GoToTop />
      </div>
    </BrowserRouter>
  )
}


