

<!-- Add this to your CSS -->
<style>
  .custom-footer {
    border-top: 1px solid rgba(0,0,0,0.1);
    background-color: var(--footer-bg);
    color: var(--footer-text);
    transition: all 0.3s ease;
  }

  [data-bs-theme="dark"] .custom-footer {
    border-top: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 -4px 20px rgba(102, 16, 242, 0.1);
  }

  .custom-footer {
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
  }

  .social-icon {
    width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
  }

  .social-icon:hover {
    transform: translateY(-2px);
  }

  [data-bs-theme="dark"] .social-icon {
    background-color: rgba(255,255,255,0.1);
    color: #fff;
  }

  [data-bs-theme="light"] .social-icon {
    background-color: rgba(0,0,0,0.1);
    color: #000;
  }

  .footer-links a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
  }

  .footer-links a:hover {
    color: #6610f2;
  }
</style>

<!-- Footer HTML -->
<footer class="custom-footer mt-auto">
  <div class="container py-5">
    <div class="row g-4">
      <!-- About Column -->
      <div class="col-md-4">
        <h4 class="mb-3">Ekklessia Management</h4>
        <p class="text-muted">Empowering church communities through faithful stewardship and digital transformation.</p>
      </div>

      <!-- Quick Links Column -->
      <div class="col-md-4">
        <h5 class="mb-3">Quick Links</h5>
        <div class="footer-links d-flex flex-column">
          <a href="#" class="mb-2">About Us</a>
          <a href="#" class="mb-2">Our Services</a>
          <a href="#" class="mb-2">Privacy Policy</a>
          <a href="#" class="mb-2">Terms of Service</a>
        </div>
      </div>

      <!-- Contact Column -->
      <div class="col-md-4">
        <h5 class="mb-3">Contact Us</h5>
        <ul class="list-unstyled">
          <li class="mb-2">
            <i class="bi bi-geo-alt me-2"></i>Shai Hills , Ghana
          </li>
          <li class="mb-2">
            <i class="bi bi-envelope me-2"></i>contact@ekklessia.org
          </li>
          <li class="mb-2">
            <i class="bi bi-phone me-2"></i>+254 700 000 000
          </li>
          <div class="d-flex gap-3 mt-3">
            <a href="#" class="social-icon">
              <i class="bi bi-facebook"></i>
            </a>
            <a href="#" class="social-icon">
              <i class="bi bi-twitter-x"></i>
            </a>
            <a href="#" class="social-icon">
              <i class="bi bi-instagram"></i>
            </a>
            <a href="#" class="social-icon">
              <i class="bi bi-youtube"></i>
            </a>
          </div>
        </ul>
      </div>
    </div>

    <hr class="my-4 opacity-50">

    <div class="text-center text-md-start">
      <small class="text-muted">
        © 2023 Ekklessia Management System. All rights reserved.
      </small>
    </div>
  </div>
</footer>