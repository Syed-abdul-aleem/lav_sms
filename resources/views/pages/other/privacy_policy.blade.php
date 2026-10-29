@extends('layouts.login_master')

@section('content')

<div class="page-content">
    <div class="content-wrapper">
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title font-weight-bold text-center">PRIVACY POLICY — BUNYADSOFT</h1>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div style="font-size: 16px;" class="col-md-10 offset-md-1">
                            <p><strong>Effective date:</strong> 29 October 2026 &nbsp;·&nbsp; <strong>Last updated:</strong> 29 October 2026</p>

                            <h4 class="font-weight-semibold">1. Who We Are</h4>
                            <p>BunyadSoft is a school management software developed by <strong>Syed Abdul Aleem</strong> in Pakistan. This Privacy Policy explains what information BunyadSoft stores, why, and how it is protected, when a school (the "School") uses the software for its students, parents/guardians and staff.</p>

                            <h4 class="font-weight-semibold">2. The School Controls Its Data</h4>
                            <p>BunyadSoft is a tool: the School decides what records to enter and who may see them. The School is the controller of its students', parents' and staff data. The Developer provides and maintains the software and does not use a School's records for advertising or sell them to anyone.</p>

                            <h4 class="font-weight-semibold">3. Information the System Stores</h4>
                            <p>Depending on how the School uses BunyadSoft, the system may store: student records (name, class, admission number, date of birth, guardian details), parent/guardian contact details (name, phone, email, address), staff records and salary information, attendance, class diary entries, examination marks and result cards, fee records, receipts and payment history, and user account details (name, email/username and securely hashed passwords). Passwords are stored only as cryptographic hashes — never in plain text.</p>

                            <h4 class="font-weight-semibold">4. How Access Is Controlled</h4>
                            <p>Every user sees only what their role allows. Parents/guardians can view only their own children's diary, results and fees. Teachers can manage only their own diary entries. Salary and payment changes are restricted to authorised roles, and payment reversals require the Super Admin. Login attempts are rate-limited to protect accounts.</p>

                            <h4 class="font-weight-semibold">5. Children's Information</h4>
                            <p>Student records are entered and managed by the School for school administration, and are visible to the student's own parents/guardians and authorised school staff. Students are not given public profiles, and children's information is never used for marketing.</p>

                            <h4 class="font-weight-semibold">6. Cookies</h4>
                            <p>BunyadSoft uses only essential session cookies required to keep you signed in securely. It does not use advertising or third-party tracking cookies.</p>

                            <h4 class="font-weight-semibold">7. Data Storage and Security</h4>
                            <p>Data is stored in a managed cloud database with access restricted to the application. Connections to BunyadSoft are served over HTTPS. We apply role-based access checks on the server for every request. No method of storage or transmission is 100% secure, but we take reasonable, current measures to protect the data entrusted to the system.</p>

                            <h4 class="font-weight-semibold">8. Sharing</h4>
                            <p>A School's data is not shared with third parties, except where required by law or where the School itself asks us in writing to export or transfer its records.</p>

                            <h4 class="font-weight-semibold">9. Your Rights and Corrections</h4>
                            <p>If you are a parent, student or staff member and your record appears in BunyadSoft, please contact your School first for any correction — the School controls its records. For questions about the software itself, contact the Developer below.</p>

                            <h4 class="font-weight-semibold">10. Changes to This Policy</h4>
                            <p>This Policy may be updated from time to time; the current version will always be on this page with its effective date.</p>

                            <h4 class="font-weight-semibold">11. Contact</h4>
                            <p><strong>BunyadSoft</strong> — Developed by Syed Abdul Aleem<br>
                            WhatsApp: <a href="https://wa.me/923295566111" target="_blank">03295566111</a><br>
                            Email: <a href="mailto:syedabdulaleem.ai@gmail.com">syedabdulaleem.ai@gmail.com</a><br>
                            Website: <a target="_blank" href="https://bunyadsoft.vercel.app">bunyadsoft.vercel.app</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
