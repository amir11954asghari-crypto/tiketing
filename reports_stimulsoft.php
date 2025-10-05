<?php
session_start();
require_once 'config.php';
require_once 'user_functions.php';
require_once 'ticket_functions.php';

// بررسی اگر کاربر وارد نشده باشد
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userFunctions = new UserFunctions();
$ticketFunctions = new TicketFunctions();
$user = $_SESSION['user'];

// تشخیص ادمین فناوری اطلاعات
$is_it_admin = ($user['user_type'] === 'admin' && isset($user['department']) && $user['department'] === 'فناوری اطلاعات');

if (!$is_it_admin) {
    header('Location: dashboard.php');
    exit;
}

// پردازش جستجو
$search_results = [];
$search_performed = false;
$search_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_name = trim($_POST['full_name']);
    $search_performed = true;
    
    if (!empty($search_name)) {
        $search_results = $ticketFunctions->searchTicketsByUserName($search_name);
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش‌گیری با Stimulsoft - سیستم مدیریت تیکت</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- Stimulsoft Reports CSS -->
    <link href="https://cdn.stimulsoft.com/reportsjs/latest/styles/stimulsoft.viewer.office2013.whiteblue.css" rel="stylesheet">
    <link href="https://cdn.stimulsoft.com/reportsjs/latest/styles/stimulsoft.designer.office2013.whiteblue.css" rel="stylesheet">
    
    <style>
        .report-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: end;
            margin-bottom: 30px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        #viewer, #designer {
            height: 800px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .hidden {
            display: none;
        }
        
        .result-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-right: 4px solid #3498db;
        }
        
        .debug-info {
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
        }
        
        .stat-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin: 0 2px;
        }
        
        .badge-new { background: #d4edda; color: #155724; }
        .badge-in-progress { background: #fff3cd; color: #856404; }
        .badge-resolved { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <header class="admin-panel">
        <div class="container">
            <div class="header-content">
                <div class="logo">سیستم مدیریت تیکت - گزارش‌گیری Stimulsoft</div>
                <div class="user-info">
                    <span>خوش آمدید، <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></span>
                    <a href="dashboard.php" class="btn btn-primary">بازگشت به پنل</a>
                    <a href="logout.php" class="btn btn-danger">خروج</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="welcome-section admin-panel">
            <h1>گزارش‌گیری پیشرفته با Stimulsoft</h1>
            <p>سیستم گزارش‌گیری حرفه‌ای با قابلیت طراحی گزارش و خروجی‌های مختلف</p>
        </div>

        <div class="report-container">
            <h2>جستجو و تولید گزارش</h2>
            
            <form method="POST" action="" class="search-form" id="searchForm">
                <div class="form-group">
                    <label for="full_name">نام و نام خانوادگی کاربر</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($search_name); ?>" 
                           placeholder="نام کامل کاربر را وارد کنید" required>
                </div>
                
                <button type="submit" name="search" class="btn btn-primary">جستجو</button>
                <button type="button" id="designerBtn" class="btn btn-success">طراحی گزارش</button>
            </form>

            <!-- ناحیه دیباگ -->
            <div id="debugInfo" class="debug-info hidden">
                <strong>اطلاعات دیباگ:</strong>
                <div id="debugContent"></div>
            </div>

            <?php if ($search_performed): ?>
                <?php if (empty($search_name)): ?>
                    <div class="notification error">
                        لطفاً نام کاربر را وارد کنید
                    </div>
                <?php elseif (empty($search_results)): ?>
                    <div class="notification error">
                        هیچ تیکتی برای کاربر "<?php echo htmlspecialchars($search_name); ?>" یافت نشد
                    </div>
                <?php else: ?>
                    <div class="result-summary">
                        <h3>نتایج جستجو برای: "<?php echo htmlspecialchars($search_name); ?>"</h3>
                        <p>تعداد تیکت‌ها: <strong><?php echo count($search_results); ?></strong></p>
                        <p>
                            جدید: <span class="stat-badge badge-new"><?php echo count(array_filter($search_results, function($t) { return $t['status'] === 'new'; })); ?></span> |
                            در دست بررسی: <span class="stat-badge badge-in-progress"><?php echo count(array_filter($search_results, function($t) { return $t['status'] === 'in-progress'; })); ?></span> |
                            حل شده: <span class="stat-badge badge-resolved"><?php echo count(array_filter($search_results, function($t) { return $t['status'] === 'resolved'; })); ?></span>
                        </p>
                    </div>

                    <div class="action-buttons">
                        <button type="button" id="viewerBtn" class="btn btn-primary">مشاهده گزارش</button>
                        <button type="button" id="designerBtn2" class="btn btn-secondary">طراحی گزارش</button>
                        <button type="button" id="pdfBtn" class="btn btn-danger">خروجی PDF</button>
                        <button type="button" id="excelBtn" class="btn btn-success">خروجی Excel</button>
                        <button type="button" id="wordBtn" class="btn btn-info">خروجی Word</button>
                        <button type="button" id="debugBtn" class="btn btn-warning">نمایش دیباگ</button>
                    </div>

                    <!-- مخفی کردن داده‌ها برای JavaScript -->
                    <div id="reportData" class="hidden">
                        <?php 
                        $json_data = json_encode($search_results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        echo htmlspecialchars($json_data); 
                        ?>
                    </div>
                    <div id="reportUserName" class="hidden"><?php echo htmlspecialchars($search_name); ?></div>
                    
                    <!-- پیام وضعیت -->
                    <div id="statusMessage" class="notification" style="display: none;"></div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- کانتینرهای Stimulsoft -->
            <div id="viewer" class="hidden"></div>
            <div id="designer" class="hidden"></div>
        </div>
    </div>

    <!-- استفاده از نسخه ساده‌تر Stimulsoft (ReportsJS) -->
    <script src="https://cdn.stimulsoft.com/reportsjs/latest/stimulsoft.reports.js"></script>
    <script src="https://cdn.stimulsoft.com/reportsjs/latest/stimulsoft.viewer.js"></script>
    <script src="https://cdn.stimulsoft.com/reportsjs/latest/stimulsoft.designer.js"></script>
    
    <script>
        // متغیرهای global
        let currentReport = null;
        let stimulsoftLoaded = false;

        // نمایش پیام وضعیت
        function showStatus(message, type = 'info') {
            const statusEl = document.getElementById('statusMessage');
            statusEl.textContent = message;
            statusEl.className = `notification ${type}`;
            statusEl.style.display = 'block';
            console.log('Status:', message);
            
            setTimeout(() => {
                statusEl.style.display = 'none';
            }, 5000);
        }

        // نمایش اطلاعات دیباگ
        function showDebugInfo() {
            const debugEl = document.getElementById('debugInfo');
            const contentEl = document.getElementById('debugContent');
            
            const dataElement = document.getElementById('reportData');
            const nameElement = document.getElementById('reportUserName');
            
            let debugInfo = '';
            
            debugInfo += 'Stimulsoft Loaded: ' + stimulsoftLoaded + '\n';
            debugInfo += 'Data Element: ' + (dataElement ? 'Exists' : 'Missing') + '\n';
            debugInfo += 'Name Element: ' + (nameElement ? 'Exists' : 'Missing') + '\n';
            
            if (dataElement) {
                try {
                    const data = JSON.parse(dataElement.textContent);
                    debugInfo += 'Data Count: ' + data.length + '\n';
                    debugInfo += 'First Item: ' + JSON.stringify(data[0]).substring(0, 100) + '\n';
                } catch (e) {
                    debugInfo += 'Data Parse Error: ' + e.message + '\n';
                }
            }
            
            contentEl.textContent = debugInfo;
            debugEl.classList.remove('hidden');
        }

        // بررسی بارگذاری Stimulsoft
        function checkStimulsoft() {
            if (typeof Stimulsoft === 'undefined') {
                showStatus('خطا: کتابخانه Stimulsoft بارگذاری نشده است', 'error');
                return false;
            }
            if (typeof Stimulsoft.Report === 'undefined') {
                showStatus('خطا: بخش Report بارگذاری نشده است', 'error');
                return false;
            }
            stimulsoftLoaded = true;
            return true;
        }

        // بررسی وجود داده‌ها
        function checkData() {
            const dataElement = document.getElementById('reportData');
            const nameElement = document.getElementById('reportUserName');
            
            if (!dataElement || !nameElement) {
                showStatus('لطفاً ابتدا جستجو کنید', 'error');
                return false;
            }
            
            try {
                const data = JSON.parse(dataElement.textContent);
                if (!data || data.length === 0) {
                    showStatus('داده‌ای برای نمایش وجود ندارد', 'error');
                    return false;
                }
                return true;
            } catch (e) {
                showStatus('خطا در خواندن داده‌ها: ' + e.message, 'error');
                return false;
            }
        }

        // ایجاد گزارش
        function createReport() {
            if (!checkStimulsoft()) return null;
            if (!checkData()) return null;
            
            const dataElement = document.getElementById('reportData');
            const nameElement = document.getElementById('reportUserName');
            
            const reportData = JSON.parse(dataElement.textContent);
            const userName = nameElement.textContent;
            
            // ایجاد گزارش جدید
            const report = new Stimulsoft.Report.StiReport();
            report.reportName = "گزارش تیکت‌های کاربر: " + userName;
            report.reportAlias = "Ticket Report";
            
            // ایجاد دیتاسورس ساده
            const dataSource = new Stimulsoft.System.Data.DataSet("Tickets");
            const dataTable = new Stimulsoft.System.Data.DataTable("Tickets");
            
            // اضافه کردن ستون‌ها
            dataTable.columns.add("Index", Stimulsoft.System.Int32);
            dataTable.columns.add("Title", Stimulsoft.System.String);
            dataTable.columns.add("UserName", Stimulsoft.System.String);
            dataTable.columns.add("Status", Stimulsoft.System.String);
            dataTable.columns.add("Priority", Stimulsoft.System.String);
            dataTable.columns.add("Category", Stimulsoft.System.String);
            
            // اضافه کردن داده‌ها
            reportData.forEach((ticket, index) => {
                const row = dataTable.rows.add();
                row.values.Index = index + 1;
                row.values.Title = ticket.title || 'بدون عنوان';
                row.values.UserName = ticket.user_full_name || 'نامشخص';
                row.values.Status = getStatusText(ticket.status);
                row.values.Priority = getPriorityText(ticket.priority);
                row.values.Category = ticket.category || 'عمومی';
            });
            
            dataSource.tables.add(dataTable);
            report.regData(dataSource.dataSetName, "", dataSource);
            
            // طراحی ساده گزارش
            const page = report.pages[0];
            
            // هدر
            const header = new Stimulsoft.Report.Components.StiText();
            header.clientRectangle = new Stimulsoft.System.Drawing.Rectangle(0, 0, page.width, 30);
            header.text = "گزارش تیکت‌ها - کاربر: " + userName;
            header.horAlignment = Stimulsoft.Report.Components.StiTextHorAlignment.Center;
            header.font = new Stimulsoft.System.Drawing.Font("Arial", 14, Stimulsoft.System.Drawing.FontStyle.Bold);
            page.components.add(header);
            
            // دیتابند
            const dataBand = new Stimulsoft.Report.Components.StiDataBand();
            dataBand.clientRectangle = new Stimulsoft.System.Drawing.Rectangle(0, 40, page.width, 20);
            dataBand.dataSourceName = "Tickets";
            page.components.add(dataBand);
            
            // محتوای دیتابند
            const indexText = new Stimulsoft.Report.Components.StiText();
            indexText.clientRectangle = new Stimulsoft.System.Drawing.Rectangle(0, 0, 30, 20);
            indexText.text = "{Index}";
            dataBand.components.add(indexText);
            
            const titleText = new Stimulsoft.Report.Components.StiText();
            titleText.clientRectangle = new Stimulsoft.System.Drawing.Rectangle(40, 0, 300, 20);
            titleText.text = "{Title}";
            dataBand.components.add(titleText);
            
            const userText = new Stimulsoft.Report.Components.StiText();
            userText.clientRectangle = new Stimulsoft.System.Drawing.Rectangle(350, 0, 200, 20);
            userText.text = "{UserName}";
            dataBand.components.add(userText);
            
            return report;
        }

        // توابع کمکی
        function getStatusText(status) {
            const statusMap = {'new': 'جدید', 'in-progress': 'در دست بررسی', 'resolved': 'حل شده'};
            return statusMap[status] || status;
        }

        function getPriorityText(priority) {
            const priorityMap = {'low': 'کم', 'medium': 'متوسط', 'high': 'بالا', 'urgent': 'فوری'};
            return priorityMap[priority] || priority;
        }

        // نمایش گزارش در ویوور
        function showReportViewer() {
            console.log('Viewer button clicked');
            showStatus('در حال ایجاد گزارش...');
            
            const report = createReport();
            if (!report) return;
            
            currentReport = report;
            
            document.getElementById('viewer').classList.remove('hidden');
            document.getElementById('designer').classList.add('hidden');
            
            const viewer = new Stimulsoft.Viewer.StiViewer(null, 'StiViewer', false);
            viewer.report = report;
            viewer.renderHtml('viewer');
            
            showStatus('گزارش با موفقیت ایجاد شد');
        }

        // نمایش دیزاینر
        function showReportDesigner() {
            console.log('Designer button clicked');
            showStatus('در حال بارگذاری طراح گزارش...');
            
            const report = createReport();
            if (!report) return;
            
            currentReport = report;
            
            document.getElementById('designer').classList.remove('hidden');
            document.getElementById('viewer').classList.add('hidden');
            
            const options = new Stimulsoft.Designer.StiDesignerOptions();
            const designer = new Stimulsoft.Designer.StiDesigner(options, 'StiDesigner', false);
            designer.report = report;
            designer.renderHtml('designer');
            
            showStatus('طراح گزارش بارگذاری شد');
        }

        // خروجی PDF
        function exportToPDF() {
            console.log('PDF button clicked');
            showStatus('در حال تولید PDF...');
            
            const report = createReport();
            if (!report) return;
            
            report.exportDocumentAsync((data) => {
                const blob = new Blob([data], {type: "application/pdf"});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = "گزارش_تیکت.pdf";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                
                showStatus('PDF با موفقیت دانلود شد');
            }, Stimulsoft.Report.StiExportFormat.Pdf);
        }

        // رویدادهای کلیک
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            
            // بررسی بارگذاری Stimulsoft
            setTimeout(() => {
                if (checkStimulsoft()) {
                    showStatus('کتابخانه Stimulsoft بارگذاری شد', 'success');
                }
            }, 1000);
            
            // اتصال رویدادها به دکمه‌ها
            document.getElementById('viewerBtn')?.addEventListener('click', showReportViewer);
            document.getElementById('designerBtn')?.addEventListener('click', showReportDesigner);
            document.getElementById('designerBtn2')?.addEventListener('click', showReportDesigner);
            document.getElementById('pdfBtn')?.addEventListener('click', exportToPDF);
            document.getElementById('excelBtn')?.addEventListener('click', () => showStatus('قابلیت Excel به زودی اضافه می‌شود'));
            document.getElementById('wordBtn')?.addEventListener('click', () => showStatus('قابلیت Word به زودی اضافه می‌شود'));
            document.getElementById('debugBtn')?.addEventListener('click', showDebugInfo);
            
            // جلوگیری از ارسال فرم با Enter
            document.getElementById('searchForm')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
            
            showStatus('صفحه آماده است');
        });

        // بررسی خطاهای جهانی
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
            showStatus('خطای JavaScript: ' + e.message, 'error');
        });
    </script>
</body>
</html>