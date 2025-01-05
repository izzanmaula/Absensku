<?php
session_start();
require ("koneksi.php");
// Jika sudah login, redirect ke beranda
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: beranda.php");
    exit();
}

$alertUsernameJudul = isset($_SESSION["alertUsernameJudul"]) ? $_SESSION["alertUsernameJudul"] :null;
// deklarasi username desc
$alertUsernameDesc = isset($_SESSION["alertUsernameDesc"]) ? $_SESSION["alertUsernameDesc"] : null;
// deklarasi error password judul 
$alertLupaPassJudul = isset($_SESSION["alertLupaPassJudul"]) ? $_SESSION["alertLupaPassJudul"] : null;
// deklrasai error password deskripsi
$alertLupaPassDesc = isset($_SESSION["alertLupaPassDesc"]) ? $_SESSION["alertLupaPassDesc"] : null;


// hapus session setelah di tampilkan
unset($_SESSION["alertUsernameJudul"]);
unset($_SESSION["alertUsernameDesc"]);
unset($_SESSION["alertLupaPassDesc"]);
unset($_SESSION["alertLupaPassJudul"]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">    <title>Masuk - Smagaedu</title>

    <title>Halosmaga - Login</title>
    <script>
    console.log('Session state:', {
        auth_token_exists: <?php echo isset($_COOKIE['auth_token']) ? 'true' : 'false' ?>,
        logged_in: <?php echo isset($_SESSION['logged_in']) ? 'true' : 'false' ?>,
        just_logged_out: <?php echo isset($_SESSION['just_logged_out']) ? 'true' : 'false' ?>
    });
</script>
</head>
<body>
  <style>
        body{ 
          font-family: merriweather;
          background-color: white ;
        }
  </style>
    
    <!-- backgrouns belakang form -->
     <div class="pt-3 px-5 text-white background">
      <div class="d-flex align-items-center gap-2">
        <img src="assets/smagalogo.png" alt="" width="50px" class="logo rounded-circle">
        <img src="assets/smagaedu.png" alt="" width="50px" class="logo rounded-circle">
      </div>
      <style>
        .logo {
          padding: 0.3rem;
          background-color: white;
        }
      </style>

        <div class="mt-2 pb-5 background-font">
          <h1 class="p-0 m-0 typing-text">
            <span>Sugeng</span>
            <span>Rawuh</span>
            <span>Bapak</span> 
            <span>Ibu</span>          
          </h1>
        </div>
     </div>
     <style>
      .background {
        z-index: 0;
        background-color:rgb(238, 236, 226);
        position: relative;
        margin-bottom: -3rem;
        height: 23rem;
      }
      .background-font{
        color: black;
      }

      /* css untuk animasi typing */
      /* styles.css */
      .typing-text {
        display: flexbox;
          gap: 0px; /* Jarak antar kata */
          font-family: merriweather;
          font-size: 50px;
          line-height: 1.3; /* Menyesuaikan jarak antar baris */
          white-space: normal; /* Mengizinkan teks melakukan wrapping */
      }

      .typing-text span {
        display: inline-block;
          opacity: 0; /* Awalnya kata tidak terlihat */
          transform: translateY(10px); /* Posisi kata sedikit di bawah */
          animation: fade-in 0.5s ease-out forwards;
      }

      /* Tambahkan delay per kata berdasarkan urutan */
      .typing-text span:nth-child(1) {
          animation-delay: 0.5s;
      }
      .typing-text span:nth-child(2) {
          animation-delay: 1s;
      }
      .typing-text span:nth-child(3) {
          animation-delay: 1.5s;
      }
      .typing-text span:nth-child(4) {
          animation-delay: 2s;
      }
      .typing-text span:nth-child(5) {
          animation-delay: 2.5s;       
      }
      .typing-text span:nth-child(6) {
          animation-delay: 3s;       
      }


      @keyframes fade-in {
          from {
              opacity: 0;
              transform: translateY(10px);
          }
          to {
              opacity: 1;
              transform: translateY(0);
          }
      }

      /* Animasi wave (goyang) */
    @keyframes wave {
        0% { transform: rotate(0); }
        25% { transform: rotate(15deg); }
        50% { transform: rotate(0); }
        75% { transform: rotate(-15deg); }
        100% { transform: rotate(0); }
    }

     </style>
    <!-- login box -->
    <main class="form-signin w-100 m-auto bg-white login-box p-5">     
      <style>
        .login-box{
          border-top-left-radius: 30px;
          border-top-right-radius: 30px;
          z-index: 1000;
          position: relative;
          box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.1);
        }
      </style>
  <form method="post" action="login_back.php">
    <!-- alert -->
    <?php
        if (isset($_SESSION['error_message'])) {
          echo '<div class="alert alert-danger" role="start">'. $_SESSION['error_message'].'</div>';
          unset($_SESSION['error_message']);
        };
        ?>


        <!-- peringatan username ga ketemu -->
        <?php if ($alertUsernameJudul !== null): ?>
            <div class="alert alert-warning mt-1" role="alert">
                <p class="p-0 m-0"><strong><?php echo $alertUsernameJudul ?></strong></p>
                <p class="p-0 m-0"><?php echo $alertUsernameDesc ?></p>
            </div> 
        <?php endif; ?>

        <!-- peringatan lupa password -->
                 <!-- peringatan username -->
        <?php if ($alertLupaPassJudul !== null): ?>
            <div class="alert alert-warning mt-1" role="alert">
                <p class="p-0 m-0"><strong><?php echo $alertLupaPassJudul ?></strong></p>
                <p class="p-0 m-0"><?php echo $alertLupaPassDesc ?></p>
            </div> 
        <?php endif; ?>

    <div class="form-floating pb-2">
      <input type="text" class="form-control username" id="username" name="username" placeholder="" required>
      <label for="username">Nama alias Anda</label>
    </div>
    <div class="form-floating">
      <input type="password" class="form-control password" id="password" name="password" placeholder="" required>
      <label for="password">Kata sandi Anda</label>
    </div>
    <div class="mt-3">
      <button class="btn w-100 py-2 login" type="submit" onclick="showSpinner(this)">
          <span class="button-text">Masuk</span>
          <div class="spinner-border spinner" role="status" style="display: none;">
              <span class="visually-hidden">Loading...</span>
          </div>
      </button>
      <style>
          .login {
              background-color: rgb(218, 119, 86);
              color: white;
              position: relative;
          }
          .spinner {
              width: 1.5rem;
              height: 1.5rem;
              color: white;
          }
          .spinner-border {
              display: inline-block;
              width: 1.5rem;
              height: 1.5rem;
              vertical-align: text-bottom;
              border: 0.2em solid currentColor;
              border-right-color: transparent;
              border-radius: 50%;
              animation: spinner-border .75s linear infinite;
          }
          @keyframes spinner-border {
              to { transform: rotate(360deg); }
          }
          .visually-hidden {
              position: absolute;
              width: 1px;
              height: 1px;
              padding: 0;
              margin: -1px;
              overflow: hidden;
              clip: rect(0,0,0,0);
              border: 0;
          }
      </style>
      <script>
          function showSpinner(button) {
              const buttonText = button.querySelector('.button-text');
              const spinner = button.querySelector('.spinner');
              
              buttonText.style.display = 'none';
              spinner.style.display = 'inline-block';
          }
      </script>
  </div>
    <p class="mt-2 mb-3 text-center text-body-secondary" style="font-size:10px;">&copy; Dikembangkan dan Dikelola oleh Tim IT SMAGA - 2024-2025</p>
  </form>
</main>
        
    </div>
</body>
</html>