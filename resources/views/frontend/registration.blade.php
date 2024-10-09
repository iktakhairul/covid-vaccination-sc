@extends('frontend.index')
@section('title', 'Contact Us | '.config('app.name'))

@section('styles')
    <style>
        .footer-section {
            padding: 150px 0;
        }
    </style>
@endsection

@section('content')

    <!-- Start Hero Section -->
    <div class="hero">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-lg-5">
                    <div class="intro-excerpt">
                        <h1>
                            COVID
                            <span class="d-block">Vaccine Registration</span>
                        </h1>
                        <p class="mb-4">
                            Welcome to the COVID Vaccine Registration portal. Please fill out the necessary information to schedule your vaccine appointment. Your health and safety are our top priority.
                        </p>
                    </div>
                    <p>
                        <a href="{{route('search.page')}}" class="btn btn-secondary me-2">Search Page</a>
                        <a href="{{route('search.page')}}" class="btn btn-white-outline">Find with NID</a>
                    </p>
                </div>
                <div class="col-lg-7">
                    <div class="hero-img-wrap">
                        <img src="{{ asset('banner.png') }}" class="img-fluid" alt="COVIT vaccination image">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Hero Section -->
    </div>
    <!-- End Hero Section -->

    <!-- Start Contact Form -->
    <div class="container-co-section mt-5 mb-5">
        <div class="container">
            <div class="block">
                <div class="row justify-content-center">

                    <div class="col-md-8 col-lg-8 pb-4">
                        <div class="row mb-5">
                            <div class="col-lg-4">
                                <div class="service no-shadow align-items-center link horizontal d-flex active" data-aos="fade-left" data-aos-delay="0">
                                    <div class="service-icon color-1 mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
                                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                        </svg>
                                    </div>
                                    <div class="service-contents">
                                        <p>H 80, Arjatpara, Mohakhali, Dhaka, Bangladesh 1212</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="service no-shadow align-items-center link horizontal d-flex active" data-aos="fade-left" data-aos-delay="0">
                                    <div class="service-icon color-1 mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                                            <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555zM0 4.697v7.104l5.803-3.558L0 4.697zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757zm3.436-.586L16 11.801V4.697l-5.803 3.546z"/>
                                        </svg>
                                    </div>
                                    <div class="service-contents">
                                        <p>contact@coviit.com</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="service no-shadow align-items-center link horizontal d-flex active" data-aos="fade-left" data-aos-delay="0">
                                    <div class="service-icon color-1 mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                                        </svg>
                                    </div>
                                    <div class="service-contents">
                                        <p>+8801683201359</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('vaccine-registration.store') }}" method="POST" id="vaccine-registration-form" onsubmit="return validateRegistrationForm()">
                            @csrf <!-- Include CSRF token for security -->

                            <div class="form-group">
                                <label class="text-black" for="vaccine_center_id">Vaccine Center</label>
                                <select class="form-control" id="vaccine_center_id" name="vaccine_center_id">
                                    <option value="">Select Vaccine Center</option>
                                    @foreach($vaccineCenters as $center)
                                        <option value="{{ $center->id }}">{{ $center->center_name }}, {{ $center->location }}</option>
                                    @endforeach
                                </select>
                                @error('vaccine_center_id')
                                <span class="text-danger"><i class="entypo-info-circled"></i> {{ $message }}</span>
                                @enderror
                                <small class="text-danger" id="vaccine_center_validation"></small>
                            </div>

                            <div class="row mt-2">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="text-black" for="full_name">Full Name</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name">
                                        @error('full_name')
                                        <span class="text-danger"><i class="entypo-info-circled"></i> {{ $message }}</span>
                                        @enderror
                                        <small class="text-danger" id="full_name_validation"></small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="text-black" for="user_nid">NID</label>
                                        <input type="text" class="form-control" id="user_nid" name="user_nid">
                                        @error('user_nid')
                                        <span class="text-danger"><i class="entypo-info-circled"></i> {{ $message }}</span>
                                        @enderror
                                        <small class="text-danger" id="nid_validation"></small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-2">
                                <label class="text-black" for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email">
                                @error('email')
                                <span class="text-danger"><i class="entypo-info-circled"></i> {{ $message }}</span>
                                @enderror
                                <small class="text-danger" id="email_validation"></small>
                            </div>

                            <div class="form-group mt-2">
                                <label class="text-black" for="phone_number">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number">
                                @error('phone_number')
                                <span class="text-danger"><i class="entypo-info-circled"></i> {{ $message }}</span>
                                @enderror
                                <small class="text-danger" id="phone_validation"></small>
                            </div>

                            <button type="button" class="btn btn-primary-hover-outline mt-3" onclick="validateRegistrationForm()">Register for Vaccine</button>
                        </form>
                    </div>
                    <div class="container-co-section d-none" id="thank-you-for-registration">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12 text-center pt-5">
                            <span class="display-3 thankyou-icon text-primary">
                                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-cart-check mb-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M11.354 5.646a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L8 8.293l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                                    <path fill-rule="evenodd" d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7.964.35A1.001 1.001 0 1 0 4 12h1a1 1 0 0 0-1.866.35zM8 10a1 1 0 0 0 0 2 1 1 0 0 0 0-2zm2.964.35A1.001 1.001 0 1 0 12 12h1a1 1 0 0 0-1.866.35zM9.5 10a1 1 0 0 0 0 2 1 1 0 0 0 0-2z"/>
                                </svg>
                            </span>
                                    <h2 class="mb-4">Thank You for Your Registration!</h2>
                                    <p>Your registration for the vaccine has been successfully completed. We will contact you shortly with more information.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
    <script>

        /**
         * Validates the registration form by calling the input validation function.
         * If all validations pass, the form is submitted.
         * @returns {boolean} - Returns true if the form is submitted successfully;
         * returns false if any validations fail, preventing submission.
         */
        function validateRegistrationForm() {
            let validationStatus = validateAllInputs(); // Call the validation function

            if (validationStatus) {
                // Submit the form if all validations pass
                document.getElementById("vaccine-registration-form").submit();
            } else {
                return false; // Prevent form submission if validations fail
            }
        }

        /**
         * Validate all input and show messages
         * @returns {boolean}
         */
        function validateAllInputs() {
            let isValid = true;

            // Select elements
            let vaccineCenter = document.getElementById("vaccine_center_id");
            let fullName = document.getElementById("full_name");
            let userNid = document.getElementById("user_nid");
            let email = document.getElementById("email");
            let phoneNumber = document.getElementById("phone_number");

            // Clear previous validation messages
            document.getElementById("vaccine_center_validation").textContent = '';
            document.getElementById("full_name_validation").textContent = '';
            document.getElementById("nid_validation").textContent = '';
            document.getElementById("email_validation").textContent = '';
            document.getElementById("phone_validation").textContent = '';

            // Validate vaccine center
            if (!vaccineCenter.value) {
                document.getElementById("vaccine_center_validation").textContent = "Please select a vaccine center";
                isValid = false;
            }

            // Validate full name
            if (!fullName.value) {
                document.getElementById("full_name_validation").textContent = "Full name is required";
                isValid = false;
            }

            // Check if the full name is at least 3 characters long
            if (fullName.value && fullName.value.length < 3) {
                document.getElementById("full_name_validation").textContent = 'This should be at least 3 characters long!';
                isValid = false;
            }

            // Validate NID
            if (!userNid.value) {
                document.getElementById("nid_validation").textContent = "NID is required";
                isValid = false;
            } else if (userNid.value.length < 10) {
                document.getElementById("nid_validation").textContent = "NID must be at least 10 characters long!";
                isValid = false;
            } else if (userNid.value.length > 20) {
                document.getElementById("nid_validation").textContent = "NID must be at less than 20 characters long!";
                isValid = false;
            } else if (!/^\d+$/.test(userNid.value)) { // Check if NID contains only numbers
                document.getElementById("nid_validation").textContent = "NID must contain numbers only!";
                isValid = false;
            }

            // Validate email
            if (!email.value) {
                document.getElementById("email_validation").textContent = "Email address is required";
                isValid = false;
            } else if (email.value.length < 8) {
                document.getElementById("email_validation").textContent = "Email address must be at least 8 characters long!";
                isValid = false;
            } else if (!validateEmail(email.value)) {
                document.getElementById("email_validation").textContent = "Please enter a valid email address!";
                isValid = false;
            }

            // Validate phone number
            if (!phoneNumber.value) {
                document.getElementById("phone_validation").textContent = "Phone number is required";
                isValid = false;
            } else if (phoneNumber.value.length < 10) {
                document.getElementById("phone_validation").textContent = "Phone number must be at least 10 characters long!";
                isValid = false;
            }

            return isValid;
        }

        /**
         * Validates an email address to check if it contains an '@' symbol
         * followed by a domain name and a top-level domain (e.g., '.com', '.org').
         * @param email - The email address to be validated.
         * @returns {boolean} - Returns true if the email is valid, false otherwise.
         */
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    </script>
@endsection
