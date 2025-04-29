<?php
// Terms & Conditions - Project Atlas eBike
session_start();
if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to terms and conditions page");
    header("Location: ../login/login_costumer.php");
    exit();
}

$date = date('d F Y');
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .terms-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem;
            min-height: 100vh;
        }

        .terms-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .terms-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .terms-header .back-btn {
            font-size: 1.5rem;
            text-decoration: none;
            color: #000;
        }

        .terms-content {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .terms-content h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
        }

        .terms-content h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .terms-content p {
            font-size: 1rem;
            color: #666;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .terms-content strong {
            color: #333;
        }

        @media (max-width: 480px) {
            .terms-container {
                padding: 1rem;
            }

            .terms-header h1 {
                font-size: 1.2rem;
            }

            .terms-content {
                padding: 1rem;
            }

            .terms-content h1 {
                font-size: 1.3rem;
            }

            .terms-content h2 {
                font-size: 1.1rem;
            }

            .terms-content p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="terms-container">
        <div class="terms-header">
            <a href="customer_dashboard.php" class="back-btn">←</a>
            <h1>Terms & Conditions</h1>
            <div style="width: 24px;"></div> <!-- Spacer to balance the header -->
        </div>

        <div class="terms-content">
            <h1>Atlas eBike</h1>
            <p><strong>Laatst bijgewerkt:</strong> <?php echo $date; ?></p>

            <h2>1. Definities</h2>
            <p>"Project Atlas" verwijst naar de fabrikant of distributeur van de eBike.<br>
                "Klant" verwijst naar de persoon die de eBike koopt of gebruikt.<br>
                "eBike" verwijst naar de Project Atlas eBike, inclusief hardware, software en accessoires.</p>

            <h2>2. Aankoop & Betaling</h2>
            <p>Alle aankopen zijn definitief na betaling en bevestiging van de bestelling. Prijzen zijn onderhevig aan wijzigingen zonder voorafgaande kennisgeving.</p>
            <p>Betaling dient volledig te worden voldaan voordat de bestelling wordt verwerkt en verzonden.</p>
            <p>Wij behouden ons het recht voor om bestellingen te annuleren in geval van verdachte activiteiten of betalingsproblemen.</p>

            <h2>3. Levering & Verzending</h2>
            <p>De geschatte levertijd wordt aangegeven bij aankoop, maar kan variëren afhankelijk van locatie en voorraad.</p>
            <p>Wij zijn niet verantwoordelijk voor vertragingen veroorzaakt door logistieke partners of douanecontroles.</p>
            <p>Bij ontvangst dient de klant de eBike direct te inspecteren en eventuele schade binnen 48 uur te melden.</p>

            <h2>4. Gebruik & Veiligheid</h2>
            <p>De klant dient de eBike te gebruiken volgens de lokale wet- en regelgeving.</p>
            <p>Het is de verantwoordelijkheid van de klant om beschermende uitrusting te dragen, zoals een helm.</p>
            <p>Project Atlas is niet verantwoordelijk voor ongevallen als gevolg van onjuist of onveilig gebruik.</p>
            <p>Het wordt aanbevolen om de eBike regelmatig te laten onderhouden om de veiligheid te waarborgen.</p>

            <h2>5. Garantie & Retourbeleid</h2>
            <p>De eBike wordt geleverd met een garantieperiode van <strong> 2 jaren</strong> op fabricagefouten.</p>
            <p>De garantie dekt geen schade veroorzaakt door verkeerd gebruik, ongelukken of normale slijtage.</p>
            <p>Retourneren kan binnen <strong>30 dagen</strong> na levering, mits de eBike ongebruikt en in originele verpakking is.</p>
            <p>Retourzendingen worden alleen geaccepteerd na voorafgaande goedkeuring en instructies van Project Atlas.</p>

            <h2>6. Onderhoud & Reparatie</h2>
            <p>De klant is verantwoordelijk voor regelmatig onderhoud van de eBike om optimale prestaties te garanderen.</p>
            <p>Project Atlas biedt onderhoudsservices en originele onderdelen aan via erkende servicepunten.</p>
            <p>Reparaties uitgevoerd door niet-erkende technici kunnen de garantie ongeldig maken.</p>

            <h2>7. Aansprakelijkheid</h2>
            <p>Project Atlas is niet aansprakelijk voor indirecte, incidentele of gevolgschade die voortkomt uit het gebruik van de eBike.</p>
            <p>De maximale aansprakelijkheid is beperkt tot de aankoopprijs van de eBike.</p>
            <p>Wij zijn niet verantwoordelijk voor schade veroorzaakt door verkeerde montage, onjuist onderhoud of modificaties door derden.</p>

            <h2>8. Intellectueel Eigendom</h2>
            <p>Alle ontwerpen, software en technologieën die deel uitmaken van de eBike blijven eigendom van Project Atlas.</p>
            <p>Het is verboden om de eBike te reverse-engineeren, kopiëren of zonder toestemming commercieel te gebruiken.</p>

            <h2>9. Privacybeleid</h2>
            <p>Wij respecteren uw privacy en verzamelen alleen de noodzakelijke gegevens voor bestelling en service.</p>
            <p>Persoonlijke gegevens worden niet verkocht aan derden en worden beschermd volgens geldende regelgeving.</p>
            <p>Voor meer details kunt u ons privacybeleid raadplegen op <strong>Privacybeleid</strong>.</p>

            <h2>10. Wijzigingen in de Voorwaarden</h2>
            <p>Project Atlas behoudt zich het recht voor om deze Voorwaarden op elk moment te wijzigen.</p>
            <p>Wijzigingen worden van kracht zodra ze op onze website zijn gepubliceerd.</p>
            <p>Het is de verantwoordelijkheid van de klant om regelmatig de Voorwaarden te controleren op updates.</p>

            <h2>11. Toepasselijk Recht & Geschillen</h2>
            <p>Op deze Voorwaarden is het recht van <strong>Nederland</strong> van toepassing.</p>
            <p>Geschillen worden in eerste instantie via bemiddeling opgelost.</p>
            <p>Indien dit niet lukt, worden geschillen beslecht door de bevoegde rechter in <strong>Nederland</strong>.</p>

            <p><strong>Contact:</strong> Voor vragen kunt u contact opnemen via <strong>Oumar Bajabour</strong>.</p>
        </div>
    </div>
</body>

</html>