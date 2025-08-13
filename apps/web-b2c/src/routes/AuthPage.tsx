import { useState, useEffect } from "react"
import { clearToken, fetchMe, getToken, login, setToken } from "../auth"
import Header from "../components/Header"

export default function AuthPage() {
  const [isLogin, setIsLogin] = useState(true)
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [name, setName] = useState("")
  const [message, setMessage] = useState("")
  const [user, setUser] = useState<any>(null)
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    setMessage("")

    try {
      if (isLogin) {
        const token = await login(email, password)
        if (token) {
          setMessage("Connexion réussie !")
          const userData = await fetchMe()
          setUser(userData)
        }
      } else {
        // Registration logic would go here
        setMessage("Inscription réussie !")
      }
    } catch (error) {
      setMessage("Erreur: " + (error as Error).message)
    } finally {
      setLoading(false)
    }
  }

  const handleLogout = () => {
    clearToken()
    setUser(null)
    setMessage("Déconnexion réussie")
  }

  // Check if user is already logged in
  useEffect(() => {
    const token = getToken()
    if (token) {
      fetchMe().then(setUser).catch(() => clearToken())
    }
  }, [])

  if (user) {
    return (
      <div className="space-y-8">
        <Header 
          title="Profil Utilisateur" 
          description="Gérez votre compte et vos informations"
        />
        
        <div className="max-w-md mx-auto">
          <div className="card p-6">
            <h2 className="text-2xl font-bold text-gray-900 mb-4">Profil Utilisateur</h2>
            <div className="space-y-3">
              <p><strong>Email:</strong> {user.email}</p>
              <p><strong>Nom:</strong> {user.name}</p>
              <button
                onClick={handleLogout}
                className="btn-primary w-full"
              >
                Se déconnecter
              </button>
            </div>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-8">
      <Header 
        title={isLogin ? "Connexion" : "Inscription"} 
        description="Accédez à votre espace personnel"
      />
      
      <div className="max-w-md mx-auto">
        <div className="card p-6">
          <h2 className="text-2xl font-bold text-gray-900 mb-6 text-center">
            {isLogin ? "Connexion" : "Inscription"}
          </h2>
          
          {message && (
            <div className={`p-3 rounded-lg mb-4 ${
              message.includes("Erreur") 
                ? "bg-red-100 text-red-700 border border-red-200" 
                : "bg-green-100 text-green-700 border border-green-200"
            }`}>
              {message}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            {!isLogin && (
              <div>
                <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
                  Nom complet
                </label>
                <input
                  type="text"
                  id="name"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                  required
                />
              </div>
            )}
            
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
                Email
              </label>
              <input
                type="email"
                id="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                required
              />
            </div>
            
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-1">
                Mot de passe
              </label>
              <input
                type="password"
                id="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary focus:border-transparent"
                required
              />
            </div>
            
            <button
              type="submit"
              disabled={loading}
              className="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? "Chargement..." : (isLogin ? "Se connecter" : "S'inscrire")}
            </button>
          </form>
          
          <div className="mt-4 text-center">
            <button
              onClick={() => setIsLogin(!isLogin)}
              className="text-setrag-primary hover:text-setrag-primary-dark text-sm"
            >
              {isLogin ? "Pas de compte ? S'inscrire" : "Déjà un compte ? Se connecter"}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}


