@extends('layouts.app')

@section('title', 'Paiement - SETRAG')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="{{ route('book') }}" class="flex items-center space-x-2 text-setrag-primary hover:text-setrag-primary-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Retour à la recherche</span>
        </a>
    </div>
    <h1 class="text-3xl font-bold text-gray-900">Paiement</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Booking Summary -->
        <div class="card">
            <h2 class="text-xl font-bold mb-4">Résumé de la réservation</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Trajet:</span>
                    <span class="font-medium">{{ $trip->originStation->name }} → {{ $trip->destinationStation->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Départ:</span>
                    <span>{{ $trip->departure_time->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Arrivée:</span>
                    <span>{{ $trip->arrival_time->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Passagers:</span>
                    <span>{{ $bookingInfo['passengers'] ?? 1 }}</span>
                </div>
                <div id="price-summary" class="border-t pt-3 mt-3">
                    <div class="flex justify-between mb-2">
                        <span>Prix de base:</span>
                        <span>{{ number_format($quote['base_price'] ?? 0, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @if(isset($quote['discount_amount']) && $quote['discount_amount'] > 0)
                        <div class="flex justify-between mb-2 text-green-600">
                            <span>Réduction ({{ $quote['passenger_type'] === 'student' ? 'Étudiant' : ($quote['passenger_type'] === 'senior' ? '3e âge' : 'Enfant') }}):</span>
                            <span>-{{ number_format($quote['discount_amount'], 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    <div class="flex justify-between mb-2">
                        <span>Commission (5%):</span>
                        <span>{{ number_format($quote['commission'] ?? ($quote['taxes'] ?? 0), 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg border-t pt-2">
                        <span>Total:</span>
                        <span class="text-setrag-primary">{{ number_format($quote['total_price'] ?? 0, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="card">
            <h2 class="text-xl font-bold mb-4">Informations de paiement</h2>
            
            <form method="POST" action="{{ route('payment.process') }}" id="payment-form">
                @csrf
                <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                <input type="hidden" name="passengers" value="{{ $bookingInfo['passengers'] ?? 1 }}">

                <!-- Classe -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Classe *</label>
                    <select name="class" id="class-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" required>
                        <option value="second_class" {{ ($bookingInfo['class'] ?? 'second_class') === 'second_class' ? 'selected' : '' }}>2ème classe</option>
                        <option value="first_class" {{ ($bookingInfo['class'] ?? '') === 'first_class' ? 'selected' : '' }}>1ère classe (+50%)</option>
                        <option value="VIP" {{ ($bookingInfo['class'] ?? '') === 'VIP' ? 'selected' : '' }}>VIP (45 000 - 100 000 FCFA)</option>
                    </select>
                </div>

                <!-- Type de passager -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de passager *</label>
                    <select name="passenger_type" id="passenger-type-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" required>
                        <option value="adult" {{ ($bookingInfo['passenger_type'] ?? 'adult') === 'adult' ? 'selected' : '' }}>Adulte</option>
                        <option value="student" {{ ($bookingInfo['passenger_type'] ?? '') === 'student' ? 'selected' : '' }}>Étudiant (-10%)</option>
                        <option value="senior" {{ ($bookingInfo['passenger_type'] ?? '') === 'senior' ? 'selected' : '' }}>3e âge (-30%)</option>
                        <option value="child" {{ ($bookingInfo['passenger_type'] ?? '') === 'child' ? 'selected' : '' }}>Enfant (-100% si < 5 ans)</option>
                    </select>
                </div>

                <!-- Date de naissance (si enfant) -->
                <div class="mb-4" id="birth-date-container" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance *</label>
                    <input type="date" name="passenger_birth_date" id="passenger_birth_date" 
                           value="{{ old('passenger_birth_date', $bookingInfo['passenger_birth_date'] ?? '') }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary">
                    <p class="text-xs text-gray-500 mt-1">Les enfants de moins de 5 ans voyagent gratuitement mais doivent être accompagnés d'un adulte.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet du passager *</label>
                    <input type="text" name="passenger_name" value="{{ old('passenger_name', session('user')['full_name'] ?? '') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email du passager *</label>
                    <input type="email" name="passenger_email" value="{{ old('passenger_email', session('user')['email'] ?? '') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-setrag-primary" 
                           required>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Mode de paiement *</label>
                    <div class="grid grid-cols-1 gap-3" id="payment-methods">
                        <!-- Visa/Mastercard -->
                        <label class="payment-method-option cursor-pointer border-2 rounded-lg p-4 transition-all duration-200" data-method="card">
                            <input type="radio" name="payment_method" value="card" class="hidden payment-method-radio" required>
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                                            <path d="M2 10h20"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">Visa / Mastercard</div>
                                    <div class="text-sm text-gray-600">Paiement par carte bancaire</div>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 payment-method-check flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Airtel Money -->
                        <label class="payment-method-option cursor-pointer border-2 rounded-lg p-4 transition-all duration-200" data-method="airtel">
                            <input type="radio" name="payment_method" value="airtel" class="hidden payment-method-radio" required>
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-red-600" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="12" r="10"/>
                                            <text x="12" y="16" text-anchor="middle" fill="white" font-size="10" font-weight="bold">A</text>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">Airtel Money</div>
                                    <div class="text-sm text-gray-600">Mobile Money</div>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 payment-method-check flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Moov Money -->
                        <label class="payment-method-option cursor-pointer border-2 rounded-lg p-4 transition-all duration-200" data-method="moov">
                            <input type="radio" name="payment_method" value="moov" class="hidden payment-method-radio" required>
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="12" r="10"/>
                                            <text x="12" y="16" text-anchor="middle" fill="white" font-size="10" font-weight="bold">M</text>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">Moov Money</div>
                                    <div class="text-sm text-gray-600">Mobile Money</div>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 payment-method-check flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full">Confirmer et payer</button>
            </form>
        </div>
    </div>
</div>

<style>
.payment-method-option {
    border-color: #e5e7eb;
    background-color: #ffffff;
}
.payment-method-option:hover {
    border-color: #0B5AA2;
    background-color: #f0f7ff;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.payment-method-option.selected {
    border-color: #0B5AA2;
    background-color: #e6f2ff;
    box-shadow: 0 0 0 3px rgba(11, 90, 162, 0.1);
}
.payment-method-option.selected .payment-method-check {
    border-color: #0B5AA2;
    background-color: #0B5AA2;
}
.payment-method-option.selected .check-icon {
    display: block !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passengerTypeSelect = document.getElementById('passenger-type-select');
    const birthDateContainer = document.getElementById('birth-date-container');
    const birthDateInput = document.getElementById('passenger_birth_date');
    const classSelect = document.getElementById('class-select');
    const form = document.getElementById('payment-form');
    const paymentMethodOptions = document.querySelectorAll('.payment-method-option');
    const paymentMethodHidden = document.getElementById('payment_method_hidden');

    // Payment method selection
    paymentMethodOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove selected class from all options
            paymentMethodOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Set the radio button
            const radio = this.querySelector('.payment-method-radio');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    // Show/hide birth date for children
    passengerTypeSelect.addEventListener('change', function() {
        if (this.value === 'child') {
            birthDateContainer.style.display = 'block';
            birthDateInput.required = true;
        } else {
            birthDateContainer.style.display = 'none';
            birthDateInput.required = false;
        }
    });

    // Trigger on load
    if (passengerTypeSelect.value === 'child') {
        birthDateContainer.style.display = 'block';
        birthDateInput.required = true;
    }

    // Update price when class or passenger type changes
    [classSelect, passengerTypeSelect, birthDateInput].forEach(element => {
        if (element) {
            element.addEventListener('change', function() {
                updatePrice();
            });
        }
    });

    function updatePrice() {
        const classValue = classSelect.value;
        const passengerType = passengerTypeSelect.value;
        const birthDate = birthDateInput.value;
        const tripId = '{{ $trip->id }}';

        // Reload page with new parameters to recalculate price
        const url = new URL(window.location.href);
        url.searchParams.set('class', classValue);
        url.searchParams.set('passenger_type', passengerType);
        if (birthDate) {
            url.searchParams.set('passenger_birth_date', birthDate);
        }
        window.location.href = url.toString();
    }
});
</script>
@endsection
