import { useNavigate } from 'react-router-dom'
import Header from '../components/Header'

export default function Home() {
  const navigate = useNavigate()

  const services = [
    {
      title: "Réserver un billet",
      description: "Réservez vos billets de train en ligne facilement",
      icon: "🎫",
      path: "/book",
      color: "bg-setrag-primary"
    },
    {
      title: "Suivi en temps réel",
      description: "Suivez vos trains en temps réel sur la carte",
      icon: "🚂",
      path: "/track",
      color: "bg-setrag-secondary"
    },
    {
      title: "Expédition de colis",
      description: "Envoyez vos colis et suivez leur livraison",
      icon: "📦",
      path: "/shipping",
      color: "bg-setrag-primary-light"
    },
    {
      title: "Espace client",
      description: "Gérez votre compte et vos réservations",
      icon: "👤",
      path: "/auth",
      color: "bg-setrag-secondary-light"
    }
  ]

  return (
    <div className="space-y-12">
      <Header 
        title="Bienvenue chez SETRAG" 
        description="Votre partenaire de confiance pour le transport ferroviaire au Gabon"
      />

      {/* Hero Section */}
      <div className="text-center py-12">
        <h1 className="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
          Bienvenue chez <span className="text-setrag-primary">SETRAG</span>
        </h1>
        <p className="text-xl text-gray-600 max-w-3xl mx-auto mb-8">
          Votre partenaire de confiance pour le transport ferroviaire au Gabon. 
          Réservez vos billets, suivez vos trains et expédiez vos colis en toute simplicité.
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <button 
            onClick={() => navigate('/book')}
            className="btn-primary text-lg px-8 py-3"
          >
            Réserver maintenant
          </button>
          <button 
            onClick={() => navigate('/track')}
            className="btn-secondary text-lg px-8 py-3"
          >
            Suivre un train
          </button>
        </div>
      </div>

      {/* Services Grid */}
      <div>
        <h2 className="text-3xl font-bold text-center text-gray-900 mb-12">
          Nos Services
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {services.map((service, index) => (
            <div 
              key={index}
              className="card p-6 hover:shadow-xl transition-shadow cursor-pointer group"
              onClick={() => navigate(service.path)}
            >
              <div className={`w-16 h-16 ${service.color} rounded-full flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform`}>
                {service.icon}
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-2">
                {service.title}
              </h3>
              <p className="text-gray-600">
                {service.description}
              </p>
            </div>
          ))}
        </div>
      </div>

      {/* Why Choose SETRAG */}
      <div className="bg-gray-50 rounded-2xl p-8">
        <h2 className="text-3xl font-bold text-center text-gray-900 mb-8">
          Pourquoi choisir SETRAG ?
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div className="text-center">
            <div className="w-16 h-16 bg-setrag-primary rounded-full flex items-center justify-center text-white text-2xl mx-auto mb-4">
              ⚡
            </div>
            <h3 className="text-xl font-semibold text-gray-900 mb-2">Rapide et Fiable</h3>
            <p className="text-gray-600">
              Service de transport ferroviaire rapide et ponctuel sur tout le territoire gabonais.
            </p>
          </div>
          <div className="text-center">
            <div className="w-16 h-16 bg-setrag-secondary rounded-full flex items-center justify-center text-white text-2xl mx-auto mb-4">
              🔒
            </div>
            <h3 className="text-xl font-semibold text-gray-900 mb-2">Sécurisé</h3>
            <p className="text-gray-600">
              Paiements sécurisés et données personnelles protégées selon les standards internationaux.
            </p>
          </div>
          <div className="text-center">
            <div className="w-16 h-16 bg-setrag-primary-light rounded-full flex items-center justify-center text-white text-2xl mx-auto mb-4">
              📱
            </div>
            <h3 className="text-xl font-semibold text-gray-900 mb-2">Moderne</h3>
            <p className="text-gray-600">
              Plateforme moderne et intuitive accessible sur tous vos appareils.
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}


