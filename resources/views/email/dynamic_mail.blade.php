<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
            .header {
                position: fixed;
                top: 0px;
                left: 0;
                right: 0;
                height: 100px;
                overflow:hidden;
            }
            .footer {
                position: fixed;
                bottom: 0px;
                left: 0;
                right: 0;
                height: 100px;
                overflow:hidden;
            }
            .content {
                padding-top: 120px;
                padding-bottom: 120px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            {!! $letter_template_header !!}
        </div>

        <div class="footer">
            {!! $letter_template_footer !!}
        </div>
        <div class="content">
            {!! $html_content !!}
        </div>
    </body>
    </html>
