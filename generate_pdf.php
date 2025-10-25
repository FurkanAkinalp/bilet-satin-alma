<?php
include 'database.php';
require_role('user');

$user_id = $_SESSION['user_id'];
$ticket_id = $_GET['id'] ?? null;

if (!$ticket_id) { die("Geçersiz istek."); }

require_once('tcpdf/tcpdf.php');

try {
    $sql = "SELECT T.id as ticket_id, T.seat_number, T.paid_price, T.purchase_date, Tr.departure_location, Tr.arrival_location, Tr.departure_time, C.company_name, U.full_name, U.email FROM \"Tickets\" T JOIN \"Trips\" Tr ON T.trip_id = Tr.id JOIN \"Company\" C ON Tr.company_id = C.id JOIN \"User\" U ON T.user_id = U.id WHERE T.id = ? AND T.user_id = ?";
    $stmt = $db->prepare($sql); $stmt->execute([$ticket_id, $user_id]); $ticket = $stmt->fetch();
    if (!$ticket) { die("Bilet bulunamadı veya bu bileti görüntüleme yetkiniz yok."); }

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR); $pdf->SetAuthor('YokMuOtobüs Platformu'); $pdf->SetTitle('Otobüs Bileti - ' . $ticket['ticket_id']); $pdf->SetSubject('Otobüs Bileti');
    $pdf->setPrintHeader(false); $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->SetFont('dejavusans', '', 12, '', true);
    $pdf->AddPage();
    $pdf->SetFillColor(240, 240, 240); $pdf->SetFont('dejavusans', 'B', 20); $pdf->Cell(0, 15, 'YokMuOtobüs E-BİLET', 0, 1, 'C', true, '', 0, false, 'T', 'M'); $pdf->Ln(10);
    $pdf->SetFont('dejavusans', 'B', 14); $pdf->Cell(90, 7, 'Firma Bilgileri', 0, 0, 'L'); $pdf->Cell(90, 7, 'Yolcu Bilgileri', 0, 1, 'L');
    $pdf->SetFont('dejavusans', '', 12); $pdf->Cell(90, 7, htmlspecialchars($ticket['company_name']), 0, 0, 'L'); $pdf->Cell(90, 7, htmlspecialchars($ticket['full_name']), 0, 1, 'L'); $pdf->Cell(90, 7, 'PNR: ' . htmlspecialchars($ticket['ticket_id']), 0, 0, 'L'); $pdf->Cell(90, 7, htmlspecialchars($ticket['email']), 0, 1, 'L'); $pdf->Ln(10);
    $pdf->SetFillColor(245, 245, 245); $pdf->SetFont('dejavusans', 'B', 14); $pdf->Cell(0, 10, 'Sefer Bilgileri', 'B', 1, 'L', true); $pdf->Ln(5);
    $pdf->SetFont('dejavusans', 'B', 12); $pdf->Cell(40, 7, 'Nereden:', 0, 0, 'L'); $pdf->SetFont('dejavusans', '', 12); $pdf->Cell(0, 7, htmlspecialchars($ticket['departure_location']), 0, 1, 'L');
    $pdf->SetFont('dejavusans', 'B', 12); $pdf->Cell(40, 7, 'Nereye:', 0, 0, 'L'); $pdf->SetFont('dejavusans', '', 12); $pdf->Cell(0, 7, htmlspecialchars($ticket['arrival_location']), 0, 1, 'L');
    $pdf->SetFont('dejavusans', 'B', 12); $pdf->Cell(40, 7, 'Tarih / Saat:', 0, 0, 'L'); $pdf->SetFont('dejavusans', '', 12); $pdf->Cell(0, 7, date('d.m.Y H:i', strtotime($ticket['departure_time'])), 0, 1, 'L'); $pdf->Ln(10);
    $pdf->SetFillColor(230, 242, 255); $pdf->SetFont('dejavusans', 'B', 16); $pdf->Cell(90, 12, 'Koltuk No: ' . $ticket['seat_number'], 1, 0, 'C', true); $pdf->SetFont('dejavusans', 'B', 16); $pdf->Cell(90, 12, 'Fiyat: ' . number_format($ticket['paid_price'], 2) . ' TL', 1, 1, 'C', true);
    $pdf->Output('bilet_' . $ticket['ticket_id'] . '.pdf', 'I');
} catch (Exception $e) { die("PDF oluşturulurken bir hata oluştu: " . $e->getMessage()); }
?>