
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/../../vendor/autoload.php';

session_start();

function check_honeypot()
{
    // check the honeypot
    if (filter_has_var(INPUT_POST, 'honeypot')) {
        $honeypot = trim($_POST['honeypot']);
        if ($honeypot) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
            exit;
        }
    }
}

function send_email($from_email, $message, $subject, $recipient_email)
{
    $mail = new PHPMailer(true);

    try {
        // Gmail SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // Your actual Gmail account
        $mail->Username = $from_email; //'darklandersfr@gmail.com';

        // Google App Password (16 characters)
        $mail->Password = 'ryrwcvzehbfaxlte';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Debug
        $mail->SMTPDebug = SMTP::DEBUG_OFF;//SMTP::DEBUG_SERVER;

        // Sender: your Gmail account
        $mail->setFrom(
            $from_email, //darklandersfr@gmail.com',
            'DarkLanders'
        );

        // Recipient: your Hotmail address
        $mail->addAddress(
            $recipient_email //'jeanne.porcher@hotmail.com'
        );

        $mail->addReplyTo(
            $_POST['email'],
            $_POST['name']
        );

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "E-mail d'envoi : " . $_POST['email'] . "<br>";
        $mail->Body .= "Nom de l'envoyeur : " . $_POST['name'] . "<br>";
        $mail->Body .= "Contenu du message : " . $message;

        $mail->send();
        return true;

        //echo 'Email successfully sent!';

    } catch (Exception $e) {

        //echo 'Sending failed: ' . $mail->ErrorInfo;
        return false;
    }
}

function validate()
{

    $inputs = [];
    $errors = [];

    // validate name
    if (filter_has_var(INPUT_POST, 'name')) {
        $inputs['name'] = trim($_POST['name']);
        if (trim($inputs['name']) === '') {
            $errors['name'] = 'Entrez votre nom';
        }
    }

    // validate email
    if (filter_has_var(INPUT_POST, 'email')) {
        $inputs['email'] = trim($_POST['email']);
        // validate email
        if (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Entrez une adresse email valide';
        }
    } else {
        $errors['email'] = 'Entrez une adresse email';
    }


    // validate subject
    if (filter_has_var(INPUT_POST, 'subject')) {
        $inputs['subject'] = trim($_POST['subject']);
        if (trim($inputs['subject']) === '') {
            $errors['subject'] = 'Entrez un objet';
        }
    } else {
        $errors['subject'] = 'Entrez un objet';
    }

    // validate message
    if (filter_has_var(INPUT_POST, 'message')) {
        $inputs['message'] = trim($_POST['message']);
        if (trim($inputs['message']) === '') {
            $errors['message'] = 'Entrez un message';
        }
    } else {
        $errors['message'] = 'Entrez un message';
    }

    return [$inputs, $errors];
}


$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method === 'POST') {

    header('Content-Type: application/json; charset=UTF-8');

    $config = [
        'mail' => [
            'to_email' => 'darklandersfr@gmail.com'
        ]
    ];

    check_honeypot();

    [$inputs, $errors] = validate();

    if (empty($errors)) {

        $from_email = $config['mail']['to_email'];
        $subject = $inputs['subject'];
        $message = nl2br(htmlspecialchars(
            $inputs['message'],
            ENT_QUOTES,
            'UTF-8'
        ));

        $sent = send_email(
            $from_email,
            $message,
            $subject,
            $inputs['email']
        );

        if ($sent) {
            echo json_encode([
                'success' => true,
                'message' => 'Merci de nous avoir contactés ! Nous vous répondrons très prochainement.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Une erreur est survenue lors de l'envoi. Veuillez réessayer."
            ]);
        }

    exit;
    }
}

// if ($request_method === 'POST') {



//     // check honeypot
//     check_honeypot();

//     // validate inputs
//     [$inputs, $errors] = validate();

//     if (empty($errors)) {
//         // send email
//         $from_email = $inputs['email'];
//         $subject = $inputs['subject'];
//         $message = nl2br(htmlspecialchars($inputs['message']));

//         send_email($config['mail']['to_email'], $message, $subject, $from_email);

//         // success message
//         $_SESSION['success_message'] =  'Merci de nous avoir contactés ! Nous vous répondrons très prochainement.';
//     } else {

//         $_SESSION['error_message'] =  'Veuillez corriger les erreurs suivantes :';
//         $_SESSION['errors'] =   $errors;
//         $_SESSION['inputs'] =   $inputs;
//     }

//     exit;
// }
if ($request_method === 'GET') {

    if (isset($_SESSION['success_message'])) {
        $success_message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
    } elseif (isset($_SESSION['inputs'], $_SESSION['errors'])) {
        $error_message = $_SESSION['error_message'];
        $errors = $_SESSION['errors'];
        $inputs = $_SESSION['inputs'];
        unset($_SESSION['errors'], $_SESSION['inputs'], $_SESSION['error_message']);
    }
}

?>

 <!DOCTYPE html>
 <html lang="fr">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">

     <title>Dark Landers</title>

     <link rel="stylesheet" href="../styles/styles.css">
     <link href="https:cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="https:cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

     <script src="https:cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
 </head>

 <body>

     <header>
         <nav class="navbar navbar-expand-lg">
             <div class="container-fluid">

                 <!-- Logo -->
                 <a class="navbar-brand" href="#">
                     <img src="../../assets/dark_landers_logo_nobackground.png" alt="Dark Landers Logo"
                         style="height: 50px; width: auto;">
                 </a>

                 <!-- Toggler -->
                 <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse"
                     data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false"
                     aria-label="Toggle navigation">
                     <span class="navbar-toggler-icon"></span>
                 </button>

                 <!-- Navigation -->
                 <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                     <div class="navbar-nav ms-auto mx-auto align-items-lg-center">
                         <a class="nav-link" href="#">Accueil</a>
                         <a class="nav-link" href="#about">Qui sommes-nous ?</a>
                         <a class="nav-link" href="#to-come">Concerts à venir</a>
                         <a class="nav-link" href="#performance-videos">Prestations</a>
                         <a class="nav-link" href="#music-list">Répertoire</a>
                         <a class="nav-link" href="#contact">Contactez-nous</a>
                     </div>
                 </div>

             </div>
         </nav>

     </header>

     <main class="main">

         <section class="container py-4">
             <div class="card" id="about">
                 <div class="card-body">
                     <div class="row g-1">
                         <div class="col-md-4">
                             <img src="../../assets/group-photo.jpg" class="img-fluid rounded-start embed-responsive"
                                 alt="Dark Landers Logo">
                         </div>
                         <div class="col-md-8">
                             <div class="card-body">
                                 <h3 class="card-title fs-2">DARK LANDERS</h3>
                                 <p class="card-text fs-4">Dark Landers réunit 4 musiciens pour un set où l'énergie et le rêve du Rock,
                                     Blues,
                                     Folk
                                     britannique croise le groove d'une section rythmique. Nous proposons un répertoire composé de
                                     créations
                                     originales et de reprises, venez partager un moment de pur live !</p>

                                 <a class="btn btn-lg" style="background-color: #3b5998"
                                     href="https:www.facebook.com/people/Dark-Landers/61594253280382/" role="button"><i
                                         class="fa fa-facebook-f"></i></a>
                                 <a class="btn btn-lg" style="background-color: #ed302f;" href="#!" role="button"><i
                                         class="fa fa-youtube"></i></a>

                             </div>
                         </div>
                     </div>

                     <h5 class="mt-4">Membres du groupe</h5>
                     <div class="card-group">
                         <div class="card m-2 rounded ">
                             <img
                                 src="https:external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fmedia.istockphoto.com%2Fid%2F1327592506%2Fvector%2Fdefault-avatar-photo-placeholder-icon-grey-profile-picture-business-man.jpg%3Fs%3D612x612%26w%3D0%26k%3D20%26c%3DBpR0FVaEa5F24GIw7K8nMWiiGmbb8qmhfkpXcp1dhQg%3D&f=1&nofb=1&ipt=14ef6e2b52198fcd869c4a837c6cd2f5a47a352e94f2f258baf215472b025fd4&ipo=images"
                                 class="card-img-top" alt="photo les">
                             <div class="card-body">
                                 <h5 class="card-title">Les</h5>
                                 <p class="card-text">Compositeur / Interprète</p>
                             </div>
                         </div>

                         <div class="card m-2 rounded">
                             <img
                                 src="https:external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fmedia.istockphoto.com%2Fid%2F1327592506%2Fvector%2Fdefault-avatar-photo-placeholder-icon-grey-profile-picture-business-man.jpg%3Fs%3D612x612%26w%3D0%26k%3D20%26c%3DBpR0FVaEa5F24GIw7K8nMWiiGmbb8qmhfkpXcp1dhQg%3D&f=1&nofb=1&ipt=14ef6e2b52198fcd869c4a837c6cd2f5a47a352e94f2f258baf215472b025fd4&ipo=images"
                                 class="card-img-top" alt="photo fulvio">
                             <div class="card-body">
                                 <h5 class="card-title">Fulvio</h5>
                                 <p class="card-text">Guitariste</p>
                             </div>
                         </div>

                         <div class="card m-2 rounded">
                             <img
                                 src="https:external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fmedia.istockphoto.com%2Fid%2F1327592506%2Fvector%2Fdefault-avatar-photo-placeholder-icon-grey-profile-picture-business-man.jpg%3Fs%3D612x612%26w%3D0%26k%3D20%26c%3DBpR0FVaEa5F24GIw7K8nMWiiGmbb8qmhfkpXcp1dhQg%3D&f=1&nofb=1&ipt=14ef6e2b52198fcd869c4a837c6cd2f5a47a352e94f2f258baf215472b025fd4&ipo=images"
                                 class="card-img-top" alt="photo lolo">
                             <div class="card-body">
                                 <h5 class="card-title">Lolo</h5>
                                 <p class="card-text">Bassiste</p>
                             </div>
                         </div>

                         <div class="card m-2 rounded">
                             <img
                                 src="https:external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fmedia.istockphoto.com%2Fid%2F1327592506%2Fvector%2Fdefault-avatar-photo-placeholder-icon-grey-profile-picture-business-man.jpg%3Fs%3D612x612%26w%3D0%26k%3D20%26c%3DBpR0FVaEa5F24GIw7K8nMWiiGmbb8qmhfkpXcp1dhQg%3D&f=1&nofb=1&ipt=14ef6e2b52198fcd869c4a837c6cd2f5a47a352e94f2f258baf215472b025fd4&ipo=images"
                                 class="card-img-top" alt="photo phil">
                             <div class="card-body">
                                 <h5 class="card-title">Phil</h5>
                                 <p class="card-text">Batteur</p>
                             </div>
                         </div>
                     </div>
                 </div>
         </section>

         <section>
             <div id="to-come" class="container py-4">
                 <h3>Concerts à venir</h3>
                 <div class="list-group">
                     <a href="#" class="list-group-item list-group-item-action active" aria-current="true">
                         <div class="d-flex w-100 justify-content-between">
                             <h5 class="mb-1">Lieu</h5>
                             <small>Date</small>
                         </div>
                         <p class="mb-1">Description de la prestation.</p>
                     </a>
                     <a href="#" class="list-group-item list-group-item-action">
                         <div class="d-flex w-100 justify-content-between">
                             <h5 class="mb-1">Lieu</h5>
                             <small>Date</small>
                         </div>
                         <p class="mb-1">Description de la prestation.</p>
                     </a>
                     <a href="#" class="list-group-item list-group-item-action">
                         <div class="d-flex w-100 justify-content-between">
                             <h5 class="mb-1">Lieu</h5>
                             <small>Date</small>
                         </div>
                         <p class="mb-1">Description de la prestation.</p>
                     </a>
                 </div>
             </div>
         </section>
         <section>
             <div id="performance-videos" class="container py-4">
                 <h3>Prestations</h3>
                 <div id="carouselCaptions" class="carousel slide">
                     <div class="carousel-indicators">
                         <button type="button" data-bs-target="#carouselCaptions" data-bs-slide-to="0" class="active"
                             aria-current="true" aria-label="Slide 1"></button>
                         <button type="button" data-bs-target="#carouselCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
                         <button type="button" data-bs-target="#carouselCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
                     </div>
                     <div class="carousel-inner">
                         <div class="carousel-item active">
                             <div class="ratio ratio-16x9">
                                 <iframe class="rounded" width="560" height="315"
                                     src="https:www.youtube.com/embed/NpEaa2P7qZI?si=bEXczmcNHhb6ZL6r" title="YouTube video player"
                                     frameborder="0"
                                     allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                     referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                             </div>

                             <div class="carousel-caption d-none d-md-block">
                                 <h4>Date1</h4>
                                 <p>Description du concert/de l'enregistrement</p>
                             </div>
                         </div>
                         <div class="carousel-item">
                             <div class="ratio ratio-16x9">
                                 <iframe class="rounded" width="560" height="315"
                                     src="https:www.youtube.com/embed/NpEaa2P7qZI?si=bEXczmcNHhb6ZL6r" title="YouTube video player"
                                     frameborder="0"
                                     allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                     referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                             </div>

                             <div class="carousel-caption d-none d-md-block">
                                 <h5>Date2</h5>
                                 <p>Description du concert/de l'enregistrement</p>
                             </div>
                         </div>
                         <div class="carousel-item">
                             <div class="ratio ratio-16x9">
                                 <iframe class="rounded" width="560" height="315"
                                     src="https:www.youtube.com/embed/NpEaa2P7qZI?si=bEXczmcNHhb6ZL6r" title="YouTube video player"
                                     frameborder="0"
                                     allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                     referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                             </div>

                             <div class="carousel-caption d-none d-md-block">
                                 <h5>Date3</h5>
                                 <p>Description du concert/de l'enregistrement</p>
                             </div>
                         </div>
                     </div>
                     <button class="carousel-control-prev" type="button" data-bs-target="#carouselCaptions" data-bs-slide="prev">
                         <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                         <span class="visually-hidden">Précédent</span>
                     </button>
                     <button class="carousel-control-next" type="button" data-bs-target="#carouselCaptions" data-bs-slide="next">
                         <span class="carousel-control-next-icon" aria-hidden="true"></span>
                         <span class="visually-hidden">Suivant</span>
                     </button>
                 </div>
             </div>
         </section>

         <section id="music-list" class="container py-4">
             <h3>Répertoire</h3>
             <table class="table">
                 <thead>
                     <tr>
                         <th scope="col">ARTISTE/GROUPE ORIGINAL</th>
                         <th scope="col">TITRE</th>
                     </tr>
                 </thead>
                 <tbody>
                     <tr>
                         <th scope="row">1</th>
                         <td>Mark</td>
                     </tr>
                     <tr>
                         <th scope="row">2</th>
                         <td>Jacob</td>
                     </tr>
                     <tr>
                         <th scope="row">3</th>
                         <td colspan="2">Larry the Bird</td>
                     </tr>
                 </tbody>
             </table>
         </section>

         <section id="contact" class="container py-4">
             <?php if (isset($success_message)) : ?>
                 <div class="alert alert-success">
                     <?= $success_message ?>
                 </div>
             <?php endif ?>

             <?php if (isset($error_message)) : ?>
                 <div class="alert alert-danger">
                     <?= $error_message ?>
                 </div>
             <?php endif ?>


             <div class="card">
                 <div class="row align-items-stretch ">

                     <!-- Image -->
                     <div class="col-md-4">
                         <img src="../../assets/dark_landers_logo.jpg" class="contact-image rounded" alt="Dark Landers Logo">
                     </div>

                     <!-- Form -->
                     <div class="col-md-8">
                         <div class="card-body">
                             <h5 class="card-title">Contactez-nous !</h5>

                             <p>
                                 Pour toute demande d'information ou réservation,
                                 vous pouvez nous contacter par mail via le formulaire suivant :
                             </p>

                             <form id="contactForm" method="post">
                                 <div class="mb-3">
                                     <label for="name" class="form-label">Nom :</label>
                                     <input type="text" value="<?= $inputs['name'] ?? '' ?>" name="name" id="name" placeholder="Exemple : DUPONT" class="form-control">
                                     <small><?= $errors['name'] ?? '' ?></small>
                                 </div>

                                 <div class="mb-3">
                                     <label for="email" class="form-label">Email :</label>
                                     <input type="email" name="email" id="email" value="<?= $inputs['email'] ?? '' ?>" placeholder="Exemple : monsieur.dupont@gmail.fr" class="form-control">
                                     <small><?= $errors['email'] ?? '' ?></small>
                                 </div>

                                 <div class="mb-3">
                                     <label for="subject" class="form-label">Objet :</label>
                                     <input type="text" name="subject" id="subject" value="<?= $inputs['subject'] ?? '' ?>" placeholder="Objet de votre demande" class="form-control">
                                     <small><?= $errors['subject'] ?? '' ?></small>
                                 </div>

                                 <div class="mb-3">
                                     <label for="message" class="form-label">Message :</label>
                                     <textarea id="message" name="message" rows="5" placeholder="Votre message..." class="form-control"><?= $inputs['message'] ?? '' ?></textarea>
                                     <small><?= $errors['message'] ?? '' ?></small>
                                 </div>

                                 <label for="honeypot" aria-hidden="true" class="user-cannot-see form-label" style="display:none"> Nickname
                                     <input type="text" name="honeypot" id="honeypot" class="form-control user-cannot-see" tabindex="-1" autocomplete="off">
                                 </label>

                                 <button type="submit" class="btn btn-dark btn-lg">Envoyer</button>
                                 <div id="formResponse"></div>
                             </form>
                         </div>
                     </div>
                 </div>
             </div>
         </section>
     </main>

     <footer id="footer" class="footer position-relative text-center">
         <div class="container">
             <div class="copyright">
                 <span>Copyright 2027 </span> <strong class="px-1 sitename">Jeanne PORCHER</strong> <span>Tous droits
                     réservés</span>
             </div>
             <div class="credits">
                 Créé avec <a href="https:getbootstrap.com">Bootstrap</a>
             </div>
         </div>
     </footer>

 <script>
document.getElementById('contactForm')
    .addEventListener('submit', async function(e) {

        e.preventDefault();

        const form = this;
        const responseDiv = document.getElementById('formResponse');

        responseDiv.innerHTML = 'Envoi en cours...';

        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: new FormData(form)
            });

            const result = await response.json();

            if (result.success) {
                responseDiv.innerHTML =
                    '<div class="alert alert-success">' +
                    result.message +
                    '</div>';

                form.reset();

            } else {
                responseDiv.innerHTML =
                    '<div class="alert alert-danger">' +
                    result.message +
                    '</div>';
            }

        } catch (error) {
            responseDiv.innerHTML =
                '<div class="alert alert-danger">' +
                'Une erreur est survenue. Veuillez réessayer.' +
                '</div>';
        }
    });
</script>

 </body>

 </html>
