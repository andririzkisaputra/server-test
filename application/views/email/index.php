<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo isset($title) ? $title : 'Email' ?></title>
    <?php $this->load->view('email/style') ?>
  </head>
  <body class="">
    <span class="preheader"><?php echo isset($pre_title) ? $pre_title : 'Pre Title' ?></span>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
      <tr>
        <td>&nbsp;</td>
        <td class="container">
          <div class="content">
            <table role="presentation" class="main">
              <tr>
                <td class="wrapper">
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td>
                        <p class="msg-title"><?php echo isset($msg_title) ? $msg_title : 'Message Title' ?></p>
                        <?php echo isset($content) ? $content : 'Content' ?>
                        <?php if (isset($button)): ?>
                          <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary mt">
                            <tbody>
                              <tr>
                                <td align="left">
                                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                      <tr>
                                        <td> <?php echo $button ?> </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        <?php endif; ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <div class="footer">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="content-block">
                    <span class="apple-link">
                      KosTzy
                    </span>
                  </td>
                </tr>
              </table>
            </div>
          </div>
        </td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </body>
</html>
