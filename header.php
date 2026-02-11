<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School ERP System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            overflow-x: hidden;
        }

        /* --- SIDEBAR SCROLL FIX --- */
        .sidebar {
            height: 100vh;           /* Full Viewport Height */
            width: 250px;
            position: fixed;         /* Fixed position */
            top: 0;
            left: 0;
            background: #343a40;     /* Dark Grey */
            padding-top: 20px;
            overflow-y: auto;        /* Enable Vertical Scroll */
            z-index: 1000;
            transition: all 0.3s;
        }

        /* Custom Scrollbar for Sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: #2c3136;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #6c757d; 
            border-radius: 3px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #adb5bd; 
        }

        .sidebar a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 16px;
            color: #d1d1d1;
            display: block;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            color: #fff;
            border-left: 4px solid #0d6efd; /* Primary Color Accent */
        }

        .sidebar i {
            width: 30px; /* Fixed width for icons alignment */
        }

        .sidebar-header {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
            padding: 0 10px;
        }

        /* Content Area Adjustments */
        .content {
            margin-left: 250px; /* Match sidebar width */
            padding: 30px;
            min-height: 100vh;
        }

        .card-box {
            border: none;
            border-radius: 10px;
        }

        .stat-card {
            padding: 20px;
            border-radius: 10px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-card h3 { margin: 0; font-size: 32px; font-weight: bold; }
        .stat-card p { margin: 0; opacity: 0.8; }
        .stat-icon { position: absolute; top: 20px; right: 20px; font-size: 40px; opacity: 0.3; }

        /* Colors */
        .bg-blue { background: linear-gradient(45deg, #4099ff, #73b4ff); }
        .bg-green { background: linear-gradient(45deg, #2ed8b6, #59e0c5); }
        .bg-orange { background: linear-gradient(45deg, #FFB64D, #ffcb80); }
        .bg-red { background: linear-gradient(45deg, #FF5370, #ff869a); }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 0; padding: 0; }
            .content { margin-left: 0; }
        }
    </style>
</head>
<body>