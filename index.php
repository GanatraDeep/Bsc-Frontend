<!doctype html>
<html lang="en" dir="ltr">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Bsc Chaiwala</title>

   <!-- Favicon -->
   <link rel="shortcut icon" href="./assets/images/logo.png" />

   <!-- Library / Plugin Css Build -->
   <link rel="stylesheet" href="./assets/css/core/libs.min.css">

   <!-- Custom Css -->
   <link rel="stylesheet" href="./assets/css/aprycot.min.css?v=1.0.0">

   <!-- Sweetalert -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.23.0/sweetalert2.css" integrity="sha512-/j+6zx45kh/MDjnlYQL0wjxn+aPaSkaoTczyOGfw64OB2CHR7Uh5v1AML7VUybUnUTscY5ck/gbGygWYcpCA7w==" crossorigin="anonymous" referrerpolicy="no-referrer" />

   <!-- Deep Changes -->
   <style>
      .bg-circle-login::before {
         background-color: rgba(255, 243, 1);
      }
   </style>
</head>

<body class=" " data-bs-spy="scroll" data-bs-target="#elements-section" data-bs-offset="0" tabindex="0">
   <!-- loader Start -->
   <div id="loading">
      <div class="loader simple-loader">
         <svg width="300" height="100" viewBox="0 0 300 100" xmlns="http://www.w3.org/2000/svg">
            <!-- Background Ellipse -->
            <ellipse cx="150" cy="50" rx="120" ry="40" fill="#241e1e" />

            <!-- Text -->
            <text x="50%" y="55%" text-anchor="middle" fill="#ef9355" font-family="Arial, sans-serif" font-size="28"
               font-weight="bold">
               Bsc Chaiwala
            </text>
         </svg>
      </div>
   </div>
   <!-- loader END -->

   <div class="wrapper">
      <section class="container-fluid bg-circle-login" id="auth-sign">
         <div class="row align-items-center">
            <div class="col-md-12 col-lg-7 col-xl-4">
               <div class="card-body">
                  <a href="#">
                     <img src="./assets/images/logo.png" class="img-fluid" style="max-width: 20%;" alt="img4">
                  </a>
                  <h2 class="mb-2 text-center">Sign In</h2>
                  <p class="text-center">Sign in to stay connected.</p>
                  <form id="loginForm">
                     <div class="row">
                        <div class="col-lg-12">
                           <div class="form-group">
                              <label for="email" class="form-label">Email</label>
                              <input type="email" class="form-control form-control-sm" id="email"
                                 placeholder="Enter your email" required>
                              <div class="invalid-feedback">Please enter a valid email.</div>
                           </div>
                        </div>
                        <div class="col-lg-12">
                           <div class="form-group">
                              <label for="password" class="form-label">Password</label>
                              <input type="password" class="form-control form-control-sm" id="password"
                                 placeholder="Enter your password" required>
                              <div class="invalid-feedback">Password is required.</div>
                           </div>
                        </div>
                     </div>
                     <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                     </div>
                  </form>

                  <div id="loginMessage" class="mt-3 text-center text-danger"></div>

               </div>
            </div>
            <div class="col-md-12 col-lg-5 col-xl-8 d-lg-block d-none vh-100 overflow-hidden">
               <img src="./assets/images/auth/09.png" class="img-fluid sign-in-img" alt="images">

            </div>
         </div>
      </section>
   </div>

   <!-- Required Library Bundle Script -->
   <script src="./assets/js/core/libs.min.js"></script>

   <!-- External Library Bundle Script -->
   <script src="./assets/js/core/external.min.js"></script>

   <!-- Mapchart JavaScript -->
   <script src="./assets/js/charts/dashboard.js"></script>

   <!-- fslightbox JavaScript -->
   <script src="./assets/js/fslightbox.js"></script>

   <!-- app JavaScript -->
   <script src="./assets/js/app.js"></script>

   <!-- moment JavaScript -->
   <script src="./assets/vendor/moment.min.js"></script>

   <!-- sweetalert -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.23.0/sweetalert2.min.js" integrity="sha512-pnPZhx5S+z5FSVwy62gcyG2Mun8h6R+PG01MidzU+NGF06/ytcm2r6+AaWMBXAnDHsdHWtsxS0dH8FBKA84FlQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

   <script>
      document.getElementById('loginForm').addEventListener('submit', async function (e) {
         e.preventDefault(); // Prevent default form submission

         const email = document.getElementById('email').value.trim();
         const password = document.getElementById('password').value.trim();
         const loginMessage = document.getElementById('loginMessage');

         // Clear previous messages
         loginMessage.textContent = '';

         // Basic validation
         if (!email || !password) {
            loginMessage.textContent = 'Both email and password are required.';
            return;
         }

         try {
            Swal.fire({
               title: 'Logging in...',
               allowOutsideClick: false,
               didOpen: () => {
                  Swal.showLoading();
               }
            });
            setTimeout(async () => {
               const response = await fetch('http://localhost:5000/api/login', {
                  method: 'POST',
                  headers: {
                     'Content-Type': 'application/json'
                  },
                  body: JSON.stringify({ email, password })
               });

               const data = await response.json();

               if (!response.ok) {
                  loginMessage.textContent = data.message || 'Login failed';
                  return;
               }

               // Successful login
               loginMessage.textContent = '';
               Swal.fire({
                  icon: 'success',
                  title: 'Login Successful',
                  text: 'You have successfully logged in!',
                  timer: 2000,
                  showConfirmButton: false
               }).then(()=>{
                  // Store JWT token in localStorage (optional)
                  sessionStorage.setItem('token', data.token);
                  sessionStorage.setItem('name', data.user.first_name + " " + data.user.last_name);
                  sessionStorage.setItem('email', data.user.email);
                  sessionStorage.setItem('role', data.user.role);
                  sessionStorage.setItem('franchise', data.user.franchise_id);
                  sessionStorage.setItem('branch', data.user.branch_id);
      
                  // Redirect to dashboard (example)
                  window.location.href = './pages/dashboard.php';
               });
            }, 500);
         } catch (error) {
            console.error('Error:', error);
            loginMessage.textContent = 'Server error, please try again later.';
         }
      });
   </script>

</body>

</html>