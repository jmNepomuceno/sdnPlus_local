<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Referral Form</title>
  <style>
    @media print {
      body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 10px;
      }

      .label {
        background-color: #e0e6ea;
        font-weight: bold;
        }

        .highlight {
        background-color: #c6e6c3;
        text-align: center;
        font-weight: bold;
        }

        .icd {
        color: blue;
        font-weight: bold;
        }
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 14px;
      background: #fff;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 42px; /* or larger like 24px */
        font-weight: bold;
        }

    table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid black;
        border-top: 0px;
    }

    td, th {
        border: 1px solid black;
        padding: 6px;
        vertical-align: top;
        word-wrap: break-word;
        min-height: 30px; /* increase this for more height */
        }

    td:nth-child(1) {
      width: 18%;
      height: 20px;
    }

    td:nth-child(2) {
      width: 17%;
      height: 20px;
    }
    td:nth-child(3) {
      width: 15%;
      height: 20px;
    }
    td:nth-child(4) {
      width: 10%;
      height: 20px;
    }
    td:nth-child(5) {
      width: 20%;
      height: 20px;
    }
    td:nth-child(6) {
      width: 20%;
      height: 20px;
    }

    .label {
      background-color: #e0e6ea;
      font-weight: bold;
    }

    .label_title {
      background-color: #e0e6ea;
      font-weight: bold;
      text-align: center;
      font-size: 15px;
      vertical-align: middle;
    }

    .label_title2 {
      background-color: #e0e6ea;
      font-weight: bold;
      text-align: center;
      font-size: 15px;
      vertical-align: middle;
    }


    .highlight {
      background-color: #c6e6c3;
      text-align: center;
      font-weight: bold;
    }

    .icd {
      color: blue;
      font-weight: bold;
    }

    textarea {
      width: 100%;
      height: 100%;
      border: 1px solid #ccc;
      padding: 4px;
      resize: vertical;
      font-family: Arial, sans-serif;
      font-size: 12px;
      box-sizing: border-box;
    }

    .label,
    .label_title,
    .label_title2,
    .highlight {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    }


    .header-container {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      border: 2px solid black;
      height: 120px;
  }

    .side-logo {
    width: 75px;
    height: auto;
    }

    .header-title {
    font-size: 24px;
    font-weight: bold;
    margin: 0 10px;
    white-space: nowrap;
    }

    .soap-box {
      white-space: pre-wrap;
      word-wrap: break-word;
    }

  </style>
</head>
<body>

    <div class="header-container">
    <div class="header-left">
        <img src="assets/main_imgs/DOH Logo.png" alt="DOH LOGO" class="side-logo">
        <img src="assets/main_imgs/BGHMC logo hi-res.png" alt="BGHMC LOGO" class="side-logo">
    </div>
    <div class="header-title">
        <h2>Patient Referral Form</h2>
    </div>
    <div class="header-right">
        <img src="assets/main_imgs/Bagong_Pilipinas_logo.png" alt="BAGONG PILIPINAS LOGO" class="side-logo">
    </div>
    </div>


  <table>
    <tr>
      <td class="label">Patient ID:</td>
      <td class="highlight" id="hpercode"></td>
      <td class="label">Case Number:</td>
      <td class="highlight" id="referral_id"></td>
      <td class="label">Process Date/Time:</td>
      <td id="reception_time"></td>
    </tr>
    <tr>
      <td class="label">Referral Status:</td>
      <td class="highlight" id="status"></td>
      <td class="label">Age:</td>
      <td id="pat_age"></td>
      <td class="label">Processed By:</td>
      <td id="processed_by"></td>
    </tr>
    <tr>
      <td class="label">Referring Agency:</td>
      <td id="referred_by"></td>
      <td class="label">ICD-10 Diagnosis</td>
      <td colspan="3"><span class="icd" id="icd_diagnosis"></span></td>
    </tr>
    <tr>
      <td class="label">Referred By:</td>
      <td id="referring_doctor"></td>
      <td class="label" rowspan="4">SUBJECTIVE:</td>
      <td colspan="3" rowspan="4"><div id="chief_complaint_history" class="soap-box"></div></td>
    </tr>
    <tr>
      <td class="label">Mobile Number:</td>
      <td id="referred_by_no"></td>
    </tr>

    <!-- SOAP Section Begins -->
    <tr>
      <td class="label">Last Name:</td>
      <td id="patlast"></td>
    </tr>
    <tr>
      <td class="label">First Name:</td>
      <td id="patfirst"></td>

    </tr>
    <tr>
      <td class="label">Middle Name:</td>
      <td id="patmiddle"></td>
      <td class="label" rowspan="4">OBJECTIVE:</td>
      <td colspan="3" rowspan="4"><div id="pertinent_findings" class="soap-box"></div></td>
    </tr>
    <tr>
      <td class="label">Extension Name:</td>
      <td id="patsuffix"></td>
    </tr>

    <tr>
      <td class="label">Gender:</td>
      <td id="patsex"></td>
    </tr>
    <tr>
      <td class="label">Civil Status:</td>
      <td id="patcstat"></td>
    </tr>
    <tr>
      <td class="label">Religion:</td>
      <td id="relcode"></td>
      <td class="label" rowspan="4">ASSESSMENT:</td>
      <td colspan="3" rowspan="4"><div id="diagnosis" class="soap-box"></div></td>

    </tr>
    <tr>
      <td class="label">Contact No.:</td>
      <td id="pat_mobile_no"></td>
    </tr>
    <tr>
      <td class="label_title2" colspan="2">PHYSICAL EXAMINATION</td>

    </tr>

    <tr>
      <td class="label">Blood Pressure:</td>
      <td id="bp"></td>
    </tr>
    <tr>
      <td class="label">Heart Rate (HR):</td>
      <td id="hr"></td>
      <td class="label" rowspan="4">PLAN:</td>
      <td colspan="3" rowspan="4"><div id="reason" class="soap-box"></div></td>
    </tr>
    <tr>
      <td class="label">Respiratory Rate (RR):</td>
      <td id="rr"></td>
    </tr>
    <tr>
      <td class="label">Body Temperature:</td>
      <td id="temp"></td>
    </tr>
    <tr>
      <td class="label">Weight:</td>
      <td id="weight"></td>

    </tr>
    <tr>
      <td class="label">Fetal Heart Tone (OB-GYNE):</td>
      <td id="fetal_heart_tone"></td>
      <td class="label" rowspan="2">REMARKS:</td>
      <td colspan="3" rowspan="2"><div id="remarks"  class="soap-box"></div></td>
    </tr>
    <tr>
      <td class="label">Fundal Height (OB-GYNE):</td>
      <td id="fundal_height"></td>
    </tr>
    <tr>
      <td class="label_title2" colspan="2">INTERNAL EXAMINATION (OB-GYNE)</td>
      <td colspan="6" class="label_title">Approval Details</td>     
    </tr>
    <tr>
      <td class="label">Cervical Dilation:</td>
      <td id="cervical_dilation"></td>
      <td class="label">Case Category:</td>
      <td class="highlight" id="pat_class"></td>
      <td class="label">Status:</td>
      <td class="highlight" id="status2"></td>
    </tr>
    <tr>
      <td class="label">Bag of Water:</td>
      <td id="bag_water"></td>
      <td colspan="4" rowspan ="4"><strong>SDN Triage Remarks:</strong><div id="approval_details" class='soap-box'></div></td>
    </tr>

    </tr>
    <tr>
      <td class="label">Presentation:</td>
      <td id="presentation"></td>

    </tr>
    <tr>
      <td class="label">Others:</td>
      <td  id="others_ob"></td>
    </tr>

  </table>
  <div style="text-align: right;">EDT-F-40-00</div>

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  const data = <?php echo json_encode($_POST); ?>;

  $(function () {
  // Populate fields with fallback to "N/A"
  for (const key in data) {
    const $el = $('#' + key);
    if ($el.length) {
      const value = data[key] ?? ''; // handles null/undefined

      if ($el.is('textarea')) {
        $el.val(value || 'N/A');
      } else {
        $el.text(value || 'N/A');
      }
    }
  }

  // Custom formatting and fallbacks
  $('#referral_id').text((data.referral_id || 'N/A').replace(/[\[\]"]/g, ''));

  $('#status2').text(data.status || 'N/A');

  $('#icd_diagnosis').text(
    `${data.icd_diagnosis || 'N/A'} : ${data.icd_title || 'N/A'}`
  );

  $('#approval_details').val(
    `SDN Triage Remarks:\n${data.approval_details || 'N/A'}`
  );

  // Auto print
  setTimeout(() => {
    window.print();
  }, 300);

  // Auto-close after print preview
  window.onafterprint = function () {
    window.close();
  };
});

</script>



</html>
