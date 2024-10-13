document.addEventListener('DOMContentLoaded', function() {
    // Event listener for printing the Stocks report
    document.getElementById('printMonthlySalesBtn').addEventListener('click', function() {
        printMonthlySalesReport();
    });

    // Event listener for generating PDF of the Stocks report
    document.getElementById('generateMonthlySalesBtn').addEventListener('click', function() {
        generateMonthlySalesReportPDF();
    });

    // Function to print the Stocks report
    function printMonthlySalesReport() {
        var printMonthlySalesBtn = document.getElementById('printMonthlySalesBtn');
        var generateMonthlySalesBtn = document.getElementById('generateMonthlySalesBtn');
            
        // Temporarily hide buttons and heading for print
        printMonthlySalesBtn.style.display = 'none';
        generateMonthlySalesBtn.style.display = 'none';
       
    
         // Get the current date
         var today = new Date();
         var date = today.toLocaleDateString(); // Format: MM/DD/YYYY
    
        const printContent = document.querySelector('.monthly-sales-report table').outerHTML; // Select only the table
        const printWindow = window.open('', '', 'height=500,width=800');
        printWindow.document.write('<html><head><title>Monthly Sales Report</title>');
        
        // Add Bootstrap for styling the table and custom styles
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">');
        printWindow.document.write('<style>table { width: 100%; } th, td { padding: 8px; text-align: center;  border: 1px solid black; } h2 {margin-bottom:10px;} p {float:right}</style>');
        
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h2>Monthly Sales Report</h2>'); // Adding a report header
        printWindow.document.write('<p>Date: ' + date + '</p>');
        printWindow.document.write(printContent); // Include only the table
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    
        // Restore the hidden elements after printing
        printMonthlySalesBtn.style.display = 'block';
        generateMonthlySalesBtn.style.display = 'block';  
    } 

     // Function to generate PDF of the Stocks report
function generateMonthlySalesReportPDF() {
    var printMonthlySalesBtn = document.getElementById('printMonthlySalesBtn');
    var generateMonthlySalesBtn = document.getElementById('generateMonthlySalesBtn');
    var paginationToHide = document.querySelector('.pagination'); // Select pagination if it exists

    // Save original display styles
    var originalPaginationDisplay = paginationToHide ? paginationToHide.style.display : '';

    // Temporarily hide buttons and pagination for PDF generation
    printMonthlySalesBtn.style.display = 'none';
    generateMonthlySalesBtn.style.display = 'none';
    if (paginationToHide) paginationToHide.style.display = 'none'; // Hide pagination if present

    // Temporarily add styles for PDF generation
    const style = document.createElement('style');
    style.innerHTML = `
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: center; border: 1px solid black; font-family: Arial, sans-serif; font-size: 14px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h2 { font-family: Arial, sans-serif; font-size: 22px; font-weight: bold; text-align:center; margin-bottom: 10px; }
        p { text-align: right; font-family: Arial, sans-serif; margin-top: 10px; margin-bottom: 0; }
    `;
    document.head.appendChild(style);

    // Create a temporary container with only the <h2> and table
    const h2 = document.querySelector('.monthly-sales-report h2').cloneNode(true); // Clone the h2 element
    const table = document.querySelector('.monthly-sales-report table').cloneNode(true); // Clone the table element

    const tempContainer = document.createElement('div'); // Create a temporary container
    tempContainer.appendChild(h2); // Append the cloned h2
    tempContainer.appendChild(table); // Append the cloned table

    // Use the temporary container for generating the PDF
    html2pdf()
        .from(tempContainer)
        .set({
            filename: 'monthly-sales-report.pdf',
            margin: 1,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        })
        .save()
        .then(() => {
            // Restore the hidden elements after generating the PDF
            printMonthlySalesBtn.style.display = 'block';
            generateMonthlySalesBtn.style.display = 'block';
            if (paginationToHide) paginationToHide.style.display = originalPaginationDisplay; // Restore pagination's original display

            // Remove the temporary styles after generating the PDF
            document.head.removeChild(style);
        });
    }

});