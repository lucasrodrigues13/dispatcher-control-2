<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .onboarding-step {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .onboarding-step.active {
            display: block;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .role-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            overflow: hidden;
            position: relative;
        }

        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .role-card.selected {
            border-color: #6366f1;
            background: linear-gradient(145deg, #6366f1, #4f46e5);
            color: white;
        }

        .role-card input[type="radio"] {
            display: none;
        }

        .check-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 24px;
            height: 24px;
            background: #4f46e5;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .role-card.selected .check-icon {
            display: flex;
        }

        .role-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #4f46e5;
        }

        .role-card.selected .role-icon {
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .step-progress {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
        }

        .step-circle.active {
            background: #6366f1;
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <!-- Progress Steps -->
                <div class="step-progress mb-4">
                    <div class="step-circle active">1</div>
                    <div class="step-circle">2</div>
                    <!-- <div class="step-circle">3</div> -->
                </div>

                    <!-- Step 1 - Personal Information -->
                    <div class="onboarding-step active" id="step1">
                         <div class="auth-card p-5">
                            <h2 class="mb-4 text-center">What best describes your company?</h2>
                            <div class="row g-4 mb-4">
                                <!-- Dispatcher -->
                                <div class="col-lg-4">
                                    <label class="role-card card h-100 text-center p-4" onclick="selectRole(this)">
                                        <input type="radio" name="role" value="dispatcher" required hidden>
                                        <div class="check-icon"><i class="bi bi-check"></i></div>
                                        <div class="card-body">
                                            <i class="bi bi-clipboard-data role-icon"></i>
                                            <h5 class="card-title mb-3">Dispatcher</h5>
                                            <p class="card-text small">Operations management and coordination</p>
                                        </div>
                                    </label>
                                </div>
                                <!-- Carrier -->
                                <div class="col-lg-4">
                                    <label class="role-card card h-100 text-center p-4" onclick="selectRole(this)">
                                        <input type="radio" name="role" value="carrier" hidden>
                                        <div class="check-icon"><i class="bi bi-check"></i></div>
                                        <div class="card-body">
                                            <i class="bi bi-truck role-icon"></i>
                                            <h5 class="card-title mb-3">Carrier</h5>
                                            <p class="card-text small">Freight transport and logistics operations</p>
                                        </div>
                                    </label>
                                </div>
                                <!-- Broker -->
                                <div class="col-lg-4">
                                    <label class="role-card card h-100 text-center p-4" onclick="selectRole(this)">
                                        <input type="radio" name="role" value="broker" hidden>
                                        <div class="check-icon"><i class="bi bi-check"></i></div>
                                        <div class="card-body">
                                            <i class="bi bi-briefcase role-icon"></i>
                                            <h5 class="card-title mb-3">Broker</h5>
                                            <p class="card-text small">Cargo brokering and negotiations</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary col-5 col-md-4 px-4" onclick="nextStep(1)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button type="button" class="btn btn-primary col-5 col-md-4 py-3" onclick="nextStep(2)">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 - Role Selection -->
                    <div class="onboarding-step" id="step2">
                       <div class="auth-card p-5">
                            <!-- Containers ocultos inicialmente -->
                            <div id="Dispatcher" style="display: none;">
                                <h2 class="mb-4 text-center">Dispatcher information</h2>

                                <form method="POST" action="{{ route('dispatchers.store') }}">
                                    @csrf

                                    <input type="hidden" name="register_type" value="auth_register">

                                    <div class="row">
                                        <div class="row mt-3">
                                            <div class="col-md-12 mb-3">
                                                <div class="form-group">
                                                    <label for="type">Type</label>
                                                    <select name="type" id="type" class="form-control" required>
                                                        <option value="" selected disabled>Select Type</option>
                                                        <option value="Individual">Individual</option>
                                                        <option value="Company">Company</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 individual">
                                            <div class="form-group">
                                                <label for="name">Name</label>
                                                <input type="text" name="name" class="form-control" id="name" placeholder="Enter name">
                                            </div>
                                        </div>

                                        <div class="col-md-6 company">
                                            <div class="form-group">
                                                <label for="company_name">Company Name</label>
                                                <input type="text" name="company_name" class="form-control" id="company_name" placeholder="Enter company name">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                                    id="email" value="{{ old('email') }}" required>

                                                @error('email')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password">Password</label>
                                                <input type="password" name="password" class="form-control" id="password" placeholder="Enter Password">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password">Confirm Password</label>
                                                <input type="password" name="password" class="form-control" id="password" placeholder="Confirm Password">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6 individual">
                                            <div class="form-group">
                                                <label for="ssn_itin">SSN/ITIN</label>
                                                <input type="text" name="ssn_itin" class="form-control" id="ssn_itin" placeholder="Enter SSN or ITIN">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3 company">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="ein_tax_id">EIN/Tax ID</label>
                                                <input type="text" name="ein_tax_id" class="form-control" id="ein_tax_id" placeholder="Enter EIN or Tax ID">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3 company">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="departament">Departament</label>
                                                <input type="text" name="departament" class="form-control" id="departament" placeholder="Departament">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="address">Address</label>
                                                <input type="text" name="address" class="form-control" id="address" placeholder="Enter address">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="city">City</label>
                                                <input type="text" name="city" class="form-control" id="city" placeholder="Enter city">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="state">State</label>
                                                <input type="text" name="state" class="form-control" id="state" placeholder="Enter state">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="zip_code">Zip Code</label>
                                                <input type="text" name="zip_code" class="form-control" id="zip_code" placeholder="Enter zip code">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="country">Country</label>
                                                <input type="text" name="country" class="form-control" id="country" placeholder="Enter country">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="phone">Phone</label>
                                                <input type="text" name="phone" class="form-control" id="phone" placeholder="Enter phone number">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="notes">Notes</label>
                                                <textarea name="notes" class="form-control" id="notes" rows="3" placeholder="Additional notes..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary px-4" onclick="nextStep(1)">
                                            <i class="bi bi-arrow-left me-2"></i>Back
                                        </button>
                                        <button type="submit" class="btn btn-primary px-5">
                                            Finish<i class="bi bi-check2 ms-2"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div id="Carrier" style="display: none;">
                                <h2 class="mb-4 text-center">Carrier information</h2>
                                <p>
                                    Under construction
                                </p>
                            </div>

                            <div id="Broker" style="display: none;">
                                <h2 class="mb-4 text-center">Broker information</h2>
                                <p>
                                    Under construction
                                </p>
                            </div>

                        </div>
                    </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            window.nextStep = step => {
            // se for para ir ao passo 2, verifica se tem role selecionado
            if (step === 2) {
                const checked = document.querySelector('input[name="role"]:checked');
                if (!checked) {
                // exibe alerta ou mensagem de erro
                alert('Please select an item before continuing.');
                return; // cancela o avanço
                }
            }

            // se passou na validação (ou for outro passo), avança mesmo
            document.querySelectorAll('.step-circle')
                .forEach((circle, i) => circle.classList.toggle('active', i < step));
            document.querySelectorAll('.onboarding-step')
                .forEach(el => el.classList.remove('active'));
            document.getElementById(`step${step}`)
                .classList.add('active');
            };

            window.selectRole = card => {
                // Remover seleção dos outros cards
                document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');

                // Selecionar o radio button
                card.querySelector('input[type="radio"]').checked = true;

                // Obter o título do card (ex: Dispatcher, Carrier, Broker)
                const roleTitle = card.querySelector('h5.card-title').innerText.trim();

                // Esconder todos os containers
                ['Dispatcher', 'Carrier', 'Broker'].forEach(id => {
                    document.getElementById(id).style.display = 'none';
                });

                // Exibir o container correspondente
                const container = document.getElementById(roleTitle);
                if (container) {
                    container.style.display = 'block';
                }

                // Armazenar o valor para uso posterior (opcional)
                window.selectedRoleTitle = roleTitle;
            };


        });
    </script>

    <script>
        function toggleFields() {
            const type = document.getElementById('type').value;

            document.querySelectorAll('.individual, .company').forEach(el => {
                el.classList.add('d-none');
            });

            if (type === 'Individual') {
                document.querySelectorAll('.individual').forEach(el => el.classList.remove('d-none'));
            } else if (type === 'Company') {
                document.querySelectorAll('.company').forEach(el => el.classList.remove('d-none'));
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            toggleFields();
            document.getElementById('type').addEventListener('change', toggleFields);
        });
    </script>
</body>
</html>
