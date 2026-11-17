<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindLink - Informed Consent & Data Usage</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            max-width: 600px;
            width: 100%;
        }
        h2 {
            color: #1f2937;
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 22px;
        }
        .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .policy-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            height: 240px;
            overflow-y: auto;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .policy-box h4 {
            margin: 12px 0 4px 0;
            color: #111827;
            font-size: 15px;
        }
        .policy-box h4:first-child {
            margin-top: 0;
        }
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #1f2937;
        }
        .checkbox-group input {
            margin-top: 3px;
            cursor: pointer;
        }
        .button-group {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .btn-decline {
            background-color: #f3f4f6;
            color: #4b5563;
        }
        .btn-decline:hover {
            background-color: #e5e7eb;
        }
        .btn-submit {
            background-color: #2A9D8F;
            color: #ffffff;
        }
        .btn-submit:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Informed Consent & Data Usage Policy</h2>
    <p class="subtitle">Please review how your personal and counselling information will be handled before proceeding.</p>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <div id="declineAlert" class="alert-error" style="display: none;">
        Counselling services cannot proceed without your informed consent. You may review the terms again when you are ready.
    </div>

    <div class="policy-box">
        <h4>1. Purpose of Data Collection</h4>
        <p>MindLink collects your personal identification (Name, Student ID) and wellbeing concerns solely to facilitate scheduling and support sessions with authorized Telkom University counsellors.</p>

        <h4>2. Confidentiality & Privacy Boundaries</h4>
        <p>Your session notes and specific concerns are strictly confidential and accessible only to your assigned counsellor. System administrators can only view anonymized service statistics and consent timestamps.</p>

        <h4>3. Data Sharing & Legal Exceptions</h4>
        <p>Your personal data will never be shared with third parties, faculty members, or parents without your explicit consent, except in cases involving imminent risk of harm to yourself or others as required by law.</p>

        <h4>4. Non-Emergency & Non-Diagnostic Scope</h4>
        <p>MindLink provides educational literacy and referral support, not clinical diagnosis or emergency crisis intervention. For immediate emergencies, please contact campus security or national emergency hotlines.</p>
    </div>

    <form action="/submit-consent" method="POST">
        @csrf
        <div class="checkbox-group">
            <input type="checkbox" id="is_agreed" name="is_agreed" value="1" onchange="toggleSubmit()">
            <label for="is_agreed">I have read and understood how my personal and counselling data will be used, and I voluntarily agree to proceed.</label>
        </div>

        <div class="button-group">
            <button type="button" class="btn btn-decline" onclick="handleDecline()">Decline</button>
            <button type="submit" id="submitBtn" class="btn btn-submit" disabled>Agree & Continue</button>
        </div>
    </form>
</div>

<script>
    function toggleSubmit() {
        const checkbox = document.getElementById('is_agreed');
        const submitBtn = document.getElementById('submitBtn');
        const declineAlert = document.getElementById('declineAlert');
        submitBtn.disabled = !checkbox.checked;
        if (checkbox.checked) {
            declineAlert.style.display = 'none';
        }
    }

    function handleDecline() {
        document.getElementById('is_agreed').checked = false;
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('declineAlert').style.display = 'block';
    }
</script>

</body>
</html>