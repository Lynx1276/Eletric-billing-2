<?php
require_once('./tcpdf/tcpdf.php');
include '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $connection_id = $_POST['connection_id'];

    // Fetch the user_id from the connections table
    $connection_sql = "SELECT user_id FROM connections WHERE connection_id = ?";
    $stmt = $conn->prepare($connection_sql);
    $stmt->bind_param("i", $connection_id);
    $stmt->execute();
    $connection_result = $stmt->get_result();
    $connection_data = $connection_result->fetch_assoc();

    if (!$connection_data) {
        die("No connection found for the provided connection ID.");
    }
    $user_id = $connection_data['user_id'];

    // Fetch user details from the users table
    $user_sql = "SELECT name, address, phone_number AS contact_number FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user_data = $user_result->fetch_assoc();

    if (!$user_data) {
        die("No user details found for the associated user ID.");
    }

    // Fetch billing and meter data
    $billing_sql = "SELECT billing_month, units_consumed, bill_status FROM billing WHERE connection_id = ? ORDER BY billing_month DESC";
    $stmt = $conn->prepare($billing_sql);
    $stmt->bind_param("i", $connection_id);
    $stmt->execute();
    $billing_result = $stmt->get_result();
    $billing_data = $billing_result->fetch_all(MYSQLI_ASSOC);

    $meters_sql = "SELECT reading_date, current_reading FROM meters WHERE connection_id = ? ORDER BY reading_date DESC";
    $stmt = $conn->prepare($meters_sql);
    $stmt->bind_param("i", $connection_id);
    $stmt->execute();
    $meters_result = $stmt->get_result();
    $meter_data = $meters_result->fetch_all(MYSQLI_ASSOC);

    // Create PDF
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Online Electric Billing');
    $pdf->SetTitle('Connection Report');
    $pdf->SetSubject('Billing and Meter Report');
    $pdf->SetKeywords('Billing, Meter, Report');

    // Add Page
    $pdf->AddPage();

    // Add Title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Connection Report', 0, 1, 'C');
    $pdf->Ln(10);

    // User Details
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'User Details', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(5);

    $pdf->Cell(0, 10, "Name: " . htmlspecialchars($user_data['name']), 0, 1, 'L');
    $pdf->Cell(0, 10, "Address: " . htmlspecialchars($user_data['address']), 0, 1, 'L');
    $pdf->Cell(0, 10, "Contact Number: " . htmlspecialchars($user_data['contact_number']), 0, 1, 'L');
    $pdf->Ln(10);

    // Billing Details Section
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Billing Details', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(5);

    // Add Table for Billing Details
    $tbl_header = '<table border="1" cellpadding="4"><thead><tr>
        <th><strong>Billing Month</strong></th>
        <th><strong>Units Consumed</strong></th>
        <th><strong>Bill Status</strong></th>
    </tr></thead><tbody>';
    $tbl_body = '';
    foreach ($billing_data as $bill) {
        $tbl_body .= '<tr>
            <td>' . htmlspecialchars($bill['billing_month']) . '</td>
            <td>' . htmlspecialchars($bill['units_consumed']) . '</td>
            <td>' . htmlspecialchars($bill['bill_status']) . '</td>
        </tr>';
    }
    $tbl_footer = '</tbody></table>';
    $pdf->writeHTML($tbl_header . $tbl_body . $tbl_footer, true, false, false, false, '');

    $pdf->Ln(10);

    // Meter Readings Section
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Meter Readings', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(5);

    // Add Table for Meter Readings
    $tbl_header = '<table border="1" cellpadding="4"><thead><tr>
        <th><strong>Reading Date</strong></th>
        <th><strong>Current Reading</strong></th>
    </tr></thead><tbody>';
    $tbl_body = '';
    foreach ($meter_data as $meter) {
        $tbl_body .= '<tr>
            <td>' . htmlspecialchars($meter['reading_date']) . '</td>
            <td>' . htmlspecialchars($meter['current_reading']) . '</td>
        </tr>';
    }
    $tbl_footer = '</tbody></table>';
    $pdf->writeHTML($tbl_header . $tbl_body . $tbl_footer, true, false, false, false, '');

    // Output PDF
    $pdf->Output('Connection_Report.pdf', 'D');
    exit();
} else {
    header("Location: report.php");
    exit();
}
?>
