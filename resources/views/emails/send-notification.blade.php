<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style>
    img {
        width: 60px; 
        height: 60px; 
        border-radius: 8px;
    }
    .img-logo{
        margin-left: 40%;
    }
</style>
</head>
<body style="background: #e5e5e5; padding: 30px;" >

<div style="max-width: 320px; margin: 0 auto; padding: 20px; background: #fff;">
    <div class="img-logo" >
        <img src="{{URL::asset("logo/2slogo.png")}}" alt="logo" style="width: 50px; height:50px;" >
    </div>
	<h3>Votre compte à bien été crée sur l'application <a href="http://localhost:8000 ">2S Kollect</a></h3>
    <h4>Vos Informations de connexion :</h4>
	<div>
        <p>Nom Complet : <strong>{{ $data["name"]}} </strong></p>
        <p>Sexe : <strong>{{ $data["sexe"]}} </strong></p>
        <p>Matricule : <strong>{{ isset($data["registration_number"])? $data["registration_number"] : ""}}  </strong></p>
        <p>Mot de passe du compte : <strong>2s@Kollect </strong></p>
        <p>Téléphone : <strong>{{ $data["phone"]}} </strong></p>
        <p>E-mail : <strong>{{ $data["email"]}} </strong></p>
        <p>Zone de collecte : <strong>{{ $data["sector_id"]}} </strong></p>
    </div>
</div>

</body>
</html>