<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindLink - Informed Consent</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .consent-container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            max-width: 600px;
            width: 100%;
        }
        .consent-container h3 {
            color: #1f2937;
            margin-top: 0;
            margin-bottom: 24px;
            font-size: 24px;
        }
        .consent-text {
            height: 250px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .checkbox-group label {
            color: #374151;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-submit {
            background-color: #2A9D8F; 
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.2s;
        }
        .btn-submit:hover {
            background-color: #21867a;
        }
    </style>
</head>
<body>

<div class="consent-container">
    <h3>Informed Consent</h3>
    
    <!-- Form action temporarily set to # -->
    <form action="#" method="POST">
        
        <!-- Privacy Policy Text Area (PBI-08) -->
        <div class="consent-text">
            <p><strong>MindLink Counseling Services Agreement</strong></p>
            <p>By proceeding with this service, you acknowledge and agree to the following terms:</p>
            <ul>
                <li><strong>Confidentiality:</strong> Your personal data and counseling records will be kept strictly confidential in accordance with Telkom University's privacy policy.</li>
                <li><strong>Access:</strong> The information you share during counseling sessions will only be accessible to authorized university counselors.</li>
                <li><strong>Data Sharing:</strong> Your data will not be shared with any third parties without your explicit consent, except in emergency situations involving an immediate risk of harm to yourself or others.</li>
                <li><strong>Non-Diagnostic Nature:</strong> The initial wellbeing check-in is informational and does not serve as a clinical diagnosis.</li>
            </ul>
            <p><em>Please ensure you have read and understood all the terms outlined above before proceeding to schedule an appointment.</em></p>
        </div>

        <!-- Checkbox (PBI-06) -->
        <div class="checkbox-group">
            <input type="checkbox" id="agreed" name="agreed" value="1" required>
            <label for="agreed">I have read and fully agree to the informed consent.</label>
        </div>

        <button type="submit" class="btn-submit">Continue</button>
    </form>
</div>

</body>
</html>