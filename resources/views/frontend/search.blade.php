@extends('frontend.index')
@section('title', 'Search Vaccination Status | '.config('app.name'))

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
                            <span class="d-block">Vaccination Info</span>
                        </h1>
                        <p class="mb-4">
                            Welcome to the COVID Vaccine input portal. You can see your vaccination status using the form below.
                        </p>
                    </div>
                    <p>
                        <a href="{{ route('vaccine-registration.create') }}" class="btn btn-secondary me-2">Get Vaccine Now</a>
                        <a href="{{ route('vaccine-registration.create') }}" class="btn btn-white-outline">Registration</a>
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

    <!-- Start Search Form Section -->
    <div class="container-co-section mt-5 mb-5">
        <div class="container">
            <h1>Search Vaccination Status</h1>

            @if(session('status'))
                <div class="alert alert-info">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('search.page') }}" method="GET" id="vaccination-search-by-nid-form">
                <div class="form-group">
                    <label for="user_nid">Enter your NID:</label>
                    <input type="text" class="form-control" id="user_nid" name="user_nid" value="{{ request()->get('user_nid')}}">
                    @error('user_nid')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                    <small class="text-danger" id="nid_validation"></small>
                </div>

                <button type="button" class="btn btn-primary mt-3" onclick="validateSearchForm()">Check Status</button>
            </form>

            <!-- Display search results -->
            @if(!empty($user))
                <div class="mt-4">
                    <h3>Vaccination Status for NID: {{ $user->nid }}</h3>
                    <p style="margin-left: 20px"><strong>Full Name:</strong> {{ $user->full_name }}</p>
                    <p style="margin-left: 20px"><strong>Status:</strong>
                        @if(!empty($user->scheduled_vaccination_date) && \Carbon\Carbon::parse($user->scheduled_vaccination_date) > now())
                            Scheduled on {{ \Carbon\Carbon::parse($user->scheduled_vaccination_date)->format('d-m-Y') }}
                        @elseif(!empty($user->scheduled_vaccination_date) && \Carbon\Carbon::parse($user->scheduled_vaccination_date) <= now())
                            Vaccinated
                        @else
                            Not scheduled
                        @endif
                    </p>

                    <!-- Vaccine Center Details -->
                    @if($user->vaccineCenter)
                        <p style="margin-left: 20px"><strong>Center Name:</strong> {{ $user->vaccineCenter->center_name }}</p>
                        <p style="margin-left: 20px"><strong>Location:</strong> {{ $user->vaccineCenter->location }}</p>
                    @else
                        <p>No vaccine center assigned.</p>
                    @endif
                </div>
            @elseif(!empty(request()->get('user_nid')))
                <div class="mt-4">
                    <h3>Vaccination Status for NID: <strong class="text-danger">Not registered</strong></h3>
                    It looks like you haven't registered yet! To get started on your vaccination journey, please register using the link - <a style="color: #0a86d5" href="{{ route('vaccine-registration.create') }}">Register Now</a>
                </div>
            @endif
        </div>
    </div>
    <!-- End Search Form Section -->

@endsection

@section('scripts')
    <!-- Add any scripts here -->

    <script>
        /**
         * Validates the search form by calling the input validation function.
         * If all validations pass, the form is submitted.
         * @returns {boolean} - Returns true if the form is submitted successfully;
         * returns false if any validations fail, preventing submission.
         */
        function validateSearchForm() {
            let validationStatus = true;
            let userNid = document.getElementById("user_nid");
            document.getElementById("nid_validation").textContent = '';

            // validate input
            if (!userNid.value) {
                document.getElementById("nid_validation").textContent = "NID is required";
                validationStatus = false;
            } else if (userNid.value.length < 10) {
                document.getElementById("nid_validation").textContent = "NID must be at least 10 characters long!";
                validationStatus = false;
            } else if (userNid.value.length > 20) {
                document.getElementById("nid_validation").textContent = "NID must be at less than 20 characters long!";
                validationStatus = false;
            } else if (!/^\d+$/.test(userNid.value)) { // Check if NID contains only numbers
                document.getElementById("nid_validation").textContent = "NID must contain numbers only!";
                validationStatus = false;
            }

            if (validationStatus) {
                // Submit the form if all validations pass
                document.getElementById("vaccination-search-by-nid-form").submit();
            } else {
                return false; // Prevent form submission if validations fail
            }
        }
    </script>
@endsection
