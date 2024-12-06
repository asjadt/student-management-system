<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
            @page {
                margin: 50px;
            }
            .header {
                position: fixed;
                top: -40px;
                left: 0;
                right: 0;
                height: 50px;
                background-color: #f2f2f2;
                text-align: center;
                line-height: 35px;



            }
            .footer {
                position: fixed;
                bottom: -40px;
                left: 0;
                right: 0;
                height: 50px;
                background-color: #f2f2f2;
                text-align: center;
                line-height: 35px;
            }
            .content {
                margin-top: 60px;
                margin-bottom: 60px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            Header Content
        </div>

        <div class="footer">
            Footer Content
        </div>
        <div class="content">
            {!! $html_content !!}
        </div>
    </body>
    </html>

