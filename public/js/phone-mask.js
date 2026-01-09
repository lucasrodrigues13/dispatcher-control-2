/**
 * Phone Mask Utility
 * Aplica máscara XXX-XXX-XXXX com código do país fixo USA (+1)
 * Formato no banco: +1XXXXXXXXXX
 */

(function() {
    'use strict';

    // Estilos para o label do código do país
    const countryLabelStyle = `
        .phone-country-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.375rem 0.75rem;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-right: none;
            border-radius: 0.375rem 0 0 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #495057;
            min-width: 80px;
            white-space: nowrap;
            height: calc(1.5em + 0.75rem + 2px);
        }
        .phone-input-wrapper {
            display: flex;
            align-items: stretch;
            gap: 0;
        }
        .phone-input-wrapper input[type="text"]:not(.phone-country-label) {
            border-left: none;
            border-radius: 0 0.375rem 0.375rem 0;
        }
        .phone-input-wrapper input[type="text"]:not(.phone-country-label):focus {
            border-left: 1px solid #86b7fe;
        }
    `;
    
    // Adicionar estilos ao documento
    if (!document.getElementById('phone-mask-styles')) {
        const style = document.createElement('style');
        style.id = 'phone-mask-styles';
        style.textContent = countryLabelStyle;
        document.head.appendChild(style);
    }

    /**
     * Inicializa o campo de código do país fixo (+1 USA) e máscara de telefone
     * @param {string} phoneInputId - ID do input de telefone
     * @param {string} countryInputId - ID do input de código do país (será criado se não existir)
     * @param {string} defaultCountry - Código do país padrão (sempre '+1' para USA)
     */
    function initPhoneMask(phoneInputId, countryInputId = null, defaultCountry = '+1') {
        const phoneInput = document.getElementById(phoneInputId);
        if (!phoneInput) return;

        // Se countryInputId não foi fornecido, criar um baseado no phoneInputId
        if (!countryInputId) {
            countryInputId = phoneInputId.replace('phone', 'country_code').replace('mobile', 'country_code');
            // Se não contém phone ou mobile, adiciona prefixo
            if (!countryInputId.includes('country_code')) {
                countryInputId = phoneInputId + '_country_code';
            }
        }

        let countryInput = document.getElementById(countryInputId);
        const currentCountryCode = '+1'; // Sempre fixo para USA

        // Verificar se já foi inicializado (tem wrapper)
        const existingWrapper = phoneInput.closest('.phone-input-wrapper');
        if (existingWrapper) {
            return; // Já foi inicializado, não fazer nada
        }

        // Criar campo de código do país fixo se não existir
        if (!countryInput) {
            // Verificar se o input tem um container pai
            const container = phoneInput.closest('.col-md-6, .col-md-3, .mb-3, .form-group');
            
            // Criar wrapper para o campo de telefone
            const wrapper = document.createElement('div');
            wrapper.className = 'phone-input-wrapper';
            
            // Criar input hidden para código do país (para envio do formulário)
            countryInput = document.createElement('input');
            countryInput.type = 'hidden';
            countryInput.id = countryInputId;
            countryInput.name = countryInputId;
            countryInput.value = '+1';
            
            // Criar label visual para mostrar "+1 USA"
            const countryLabel = document.createElement('span');
            countryLabel.className = 'phone-country-label';
            countryLabel.textContent = '+1 USA';

            // Envolver o input e adicionar o label antes
            if (container) {
                phoneInput.parentNode.insertBefore(wrapper, phoneInput);
            } else {
                // Se não tem container, inserir antes do input
                phoneInput.parentNode.insertBefore(wrapper, phoneInput);
            }
            
            wrapper.appendChild(countryLabel);
            wrapper.appendChild(countryInput); // Input hidden para envio do formulário
            wrapper.appendChild(phoneInput);
            phoneInput.style.flex = '1';
            
            // Marcar como inicializado
            phoneInput.setAttribute('data-phone-mask-initialized', 'true');
            
            // Ajustar altura do label para corresponder ao input
            setTimeout(function() {
                const computedHeight = window.getComputedStyle(phoneInput).height;
                if (computedHeight && computedHeight !== 'auto') {
                    countryLabel.style.height = computedHeight;
                }
            }, 0);
        }

        // Aplicar máscara ao input
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
            
            // Limita a 10 dígitos (para formato US)
            if (value.length > 10) {
                value = value.substring(0, 10);
            }

            // Aplica máscara XXX-XXX-XXXX
            if (value.length > 6) {
                value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6, 10);
            } else if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            }

            e.target.value = value;
        });

        // Antes de enviar o form, combinar código do país com o número
        const form = phoneInput.closest('form');
        if (form && !form.hasAttribute('data-phone-mask-handled')) {
            form.setAttribute('data-phone-mask-handled', 'true');
            form.addEventListener('submit', function(e) {
                // Processar todos os campos de telefone no formulário
                const phoneInputs = form.querySelectorAll('input[id$="_phone"], input[id$="_mobile"], input[id="phone"], input[id="contact_phone"], input[id="accounting_phone_number"]');
                phoneInputs.forEach(function(input) {
                    const digits = input.value.replace(/\D/g, '');
                    if (digits.length === 10) {
                        // Código do país é sempre 1 (USA)
                        // Formato: código do país + 10 dígitos (ex: 15551234567)
                        input.value = '1' + digits;
                    }
                });
            });
        }

        // Se o campo já tem valor do banco (+1XXXXXXXXXX), converter para exibição
        if (phoneInput.value) {
            let value = phoneInput.value.trim();
            // Se começa com +, é formato do banco
            if (value.startsWith('+')) {
                const digits = value.replace(/\D/g, '');
                if (digits.length === 11 && digits.startsWith('1')) {
                    const phoneDigits = digits.substring(1);
                    if (phoneDigits.length === 10) {
                        phoneInput.value = phoneDigits.substring(0, 3) + '-' + 
                                          phoneDigits.substring(3, 6) + '-' + 
                                          phoneDigits.substring(6, 10);
                    }
                }
            } else if (/^\d{11}$/.test(value.replace(/\D/g, ''))) {
                // Se tem 11 dígitos sem +, também pode ser formato do banco
                const digits = value.replace(/\D/g, '');
                if (digits.startsWith('1')) {
                    const phoneDigits = digits.substring(1);
                    if (phoneDigits.length === 10) {
                        phoneInput.value = phoneDigits.substring(0, 3) + '-' + 
                                          phoneDigits.substring(3, 6) + '-' + 
                                          phoneDigits.substring(6, 10);
                    }
                }
            }
        }
    }

    // Função para inicializar automaticamente campos conhecidos
    function autoInitPhoneMasks() {
        // Inicializar campos conhecidos apenas se ainda não foram inicializados
        const phoneFields = [
            'phone', 'contact_phone', 'pickup_phone', 'pickup_mobile',
            'delivery_phone', 'delivery_mobile', 'shipper_phone',
            'accounting_phone_number'
        ];

        phoneFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !field.hasAttribute('data-phone-mask-initialized')) {
                // Verificar se já tem wrapper (já foi inicializado)
                const wrapper = field.closest('.phone-input-wrapper');
                if (!wrapper) {
                    initPhoneMask(fieldId);
                    field.setAttribute('data-phone-mask-initialized', 'true');
                }
            }
        });
    }

    // Inicializar automaticamente quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInitPhoneMasks);
    } else {
        // DOM já está pronto
        autoInitPhoneMasks();
    }

    // Expor função globalmente para uso manual
    window.initPhoneMask = initPhoneMask;
})();
