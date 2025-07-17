<?php
/*
Plugin Name: Smart EMI Calculator
Description: A simple and secure shortcode-based EMI Calculator plugin.
Version: 1.0
Author: Adireddy
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: smart-emi-calculator
*/

defined('ABSPATH') || exit;

//  Register shortcode
add_shortcode('smart_emi_calculator', 'smart_emi_calculator_shortcode');

//  Shortcode handler with output buffering
function smart_emi_calculator_shortcode() {
    ob_start();
    smart_emi_calculator_form();
    return ob_get_clean();
}

//  Main calculator form and logic
function smart_emi_calculator_form() {
    echo '<h2>' . esc_html__('Smart EMI Calculator', 'smart-emi-calculator') . '</h2>';

    //  Basic Styling
    echo '<style>
        form {
            max-width: 400px;
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin: auto;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            background: #0073aa;
            color: white;
            border: none;
            cursor: pointer;
        }
        h2 {
            text-align: center;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background: #005a8c;
        }
    </style>';

    //  Display the form
    echo '<form method="post">';
    wp_nonce_field('smart_emi_calc_action', 'smart_emi_calc_nonce'); // ✅ Nonce field
    echo '<input type="number" name="loan_amount" required placeholder="Loan Amount (₹)" step="any">';
    echo '<input type="number" name="interest_rate" required placeholder="Interest Rate (%)" step="any">';
    echo '<input type="number" name="loan_tenure" required placeholder="Loan Tenure (years)" step="any">';
    echo '<input type="submit" name="calculate_emi" value="Calculate EMI">';
    echo '</form>';

    //  Process form safely
    if (
    isset($_POST['calculate_emi']) &&
    isset($_POST['loan_amount'], $_POST['interest_rate'], $_POST['loan_tenure']) &&
    isset($_POST['smart_emi_calc_nonce']) &&
    wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smart_emi_calc_nonce'])), 'smart_emi_calc_action')
)
 {
        //  Sanitize and unslash
        $loan_amount   = floatval(sanitize_text_field(wp_unslash($_POST['loan_amount'])));
        $interest_rate = floatval(sanitize_text_field(wp_unslash($_POST['interest_rate'])));
        $loan_tenure   = floatval(sanitize_text_field(wp_unslash($_POST['loan_tenure'])));

        //  EMI Calculation
        $r = $interest_rate / (12 * 100); // Monthly interest rate
        $n = $loan_tenure * 12;           // Total months

        if ($r == 0) {
            $emi = $loan_amount / $n;
        } else {
            $emi = ($loan_amount * $r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
        }

        $emi = round($emi, 2);

        //  Display Result
        echo '<h3>' . esc_html__('Your EMI is:', 'smart-emi-calculator') . ' ₹' . number_format($emi, 2) . ' ' . esc_html__('per month', 'smart-emi-calculator') . '</h3>';
    }
}