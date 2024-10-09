<footer class="footer-section">
    <div class="container relative">
        <div class="row mb-5">
            <div class="col-lg-4">
                <div class="mb-4 footer-logo-wrap">
                    <a href="#" class="footer-logo">COVID
                        Vaccine Registration<span>.</span></a>
                </div>
                <p class="mb-4">Welcome to the COVID Vaccine Registration portal. Please fill out the necessary information to schedule your vaccine appointment. Your health and safety are our top priority.</p>
                <ul class="list-unstyled custom-social">
                    <li><a href="#" aria-label="Facebook Link"><span class="fa fa-brands fa-facebook-f"></span></a></li>
                    <li><a href="#" aria-label="Youtube Link"><span class="fa fa-brands fa-youtube"></span></a></li>
                    <li><a href="#" aria-label="Instagram Link"><span class="fa fa-brands fa-instagram"></span></a></li>
                    <li><a href="#" aria-label="Linkedin Link"><span class="fa fa-brands fa-linkedin"></span></a></li>
                </ul>
            </div>
            <div class="col-lg-8">
                <div class="row links-wrap">
                    <div class="col-md-2"></div>
                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li><a href="{{ route('vaccine-registration.create') }}">Vaccination Registration</a></li>
                            <li><a href="">Vaccine Centers</a></li>
                            <li><a href="">About Us</a></li>
                            <li><a href="">Blogs</a></li>
                            <li><a href="">Contact Us</a></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li><i class="fa fa-map-marker"></i> Dhaka, Bangladesh</li>
                            <li><i class="fa fa-phone"></i> +8801683201359</li>
                            <li><i class="fa fa-paper-plane"></i> contact@coviit.com</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-top copyright">
            <div class="row pt-4">
                <div class="col-lg-6">
                    <p class="mb-2 text-center text-lg-start">
                        Copyright &copy;2021-{{ date('Y') }}. All Rights Reserved. &mdash; Designed with love by <a href="https://github.com/iktakhairul" target="_blank">Shah Md. Iktakhairul Islam</a>
                    </p>
                </div>

                <div class="col-lg-6 text-center text-lg-end">
                    <ul class="list-unstyled d-inline-flex ms-auto">
                        <li class="me-4"><a href="">Terms &amp; Conditions</a></li>
                        <li><a href="">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</footer>
