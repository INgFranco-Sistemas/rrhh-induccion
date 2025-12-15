import axios from 'axios'

export default class FirmaPeruIntegrador {
  constructor(config) {
    let defaultValues = {
      port: 48596,
      count: 0,
      maximumTry: 60,
      host_: 'http://127.0.0.1:%s/firmaperu/sign',
      signatureInit: () => {alert("PROCESO INCIADO")},
      signatureOk: () => {},
      signatureCancel: () => {},
      getParams: () => {}
    }
    defaultValues = Object.assign(defaultValues, config)
    this.port = defaultValues.port
    this.count = defaultValues.count
    this.maximumTry = defaultValues.maximumTry
    this.inter = null
    this.signatureInit = defaultValues.signatureInit
    this.signatureOk = defaultValues.signatureOk
    this.signatureCancel = defaultValues.signatureCancel
    this.getParams = defaultValues.getParams
  }
  get host() {
    return 'http://127.0.0.1:' + this.port + '/firmaperu/sign'
  }
  get OSName() {
    let userAgent = window.navigator.userAgent
    let platform = window.navigator.platform
    let windowsPlatforms = ['Win32', 'Win64', 'Windows', 'WinCE']
    let iosPlatforms = ['iPhone', 'iPad', 'iPod']
    let macosPlatforms = ['Macintosh', 'MacIntel', 'MacPPC', 'Mac68K']
    let OSName = null
    if (macosPlatforms.indexOf(platform) != -1) {
      OSName = 'macOS'
    } else if (iosPlatforms.indexOf(platform) != -1) {
      OSName = 'iOS'
    } else if (windowsPlatforms.indexOf(platform) != -1) {
      OSName = 'Windows'
    } else if (/Android/.test(userAgent)) {
      OSName = 'Android'
    } else if (/Linux/.test(platform)) {
      OSName = 'Linux'
    } else {
      OSName = platform
    }
    return OSName
  }
  get AppFirmaPeru() {
    var clickonce =
      'https://resources.firmaperu.gob.pe/app/clickonce/clienteweb/FirmaPeruWeb.application'
    var jws = 'jnlps://apps.firmaperu.gob.pe/web/jwslauncher.jsp'
    switch (this.OSName) {
      case 'Windows': {
        console.log(
          'Firma Per\xfa: Sistema Operativo Windows, se realizar\xe1 la ejecuci\xf3n con ClickOnce.'
        )
        return clickonce
      }
      case 'macOS': {
        console.log(
          'Firma Per\xfa: Sistema Operativo macOS, se realizar\xe1 la ejecuci\xf3n con OpenWebStart.'
        )
        return jws
      }
      case 'Linux': {
        let userAgent = window.navigator.userAgent
        if (/Firefox/.test(userAgent)) {
          console.log(
            'Firma Per\xfa: Sistema Operativo Linux, se realizar\xe1 la ejecuci\xf3n con OpenWebStart.'
          )
          return jws
        } else {
          alert('Firma Per\xfa\n\nNavegador no soportado, solo disponible con Mozilla Firefox.')
          return null
        }
      }
      default:
        alert("Firma Per\xfa\n\nSistema operativo '" + this.OSName + "' no soportado")
        return null
    }
  }
  startSignature() {
    this.count = 0
    var app = this.AppFirmaPeru
    if (app != null) {
      this.signatureInit()
      clearInterval(this.inter)
      this.inter = setInterval(() => {
        console.log(this.host)
        axios
          .get(this.host, {
            headers: {
              Accept: '*/*',
              'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            crossDomain: true
          })
          .then((r) => {
            if ('ok' === r.data.status) {
              clearInterval(this.inter)
              this.processSignature()
              return
            }
          })
          .catch((e) => {
            console.log(e)
            if (this.count == 0) {
              //this.printInfo()
              this.runService()
              console.log(
                'El tiempo de espera m\xe1ximo para la ejecuci\xf3n del servicio es de 1 minuto, pasado ese tiempo si el servicio no se ejecut\xf3, la operaci\xf3n de firma digital se cancelar\xe1.'
              )
            }
            this.count++
            console.log(
              'Verificando el servicio se haya instalado, intento ' +
                this.count +
                ' de ' +
                this.maximumTry
            )
            if (this.count == this.maximumTry) {
              clearInterval(this.inter)
              console.log(
                'Tiempo de espera de ejecuci\xf3n superado. Si la instalaci\xf3n est\xe1 en progreso, espere a que esta termine y luego vuelva a intentar firmar nuevamente.'
              )
              alert(
                'Firma Per\xfa\n\nTiempo de espera de ejecuci\xf3n superado.\n\nSi la instalaci\xf3n est\xe1 en progreso, espere a que esta termine y luego vuelva a intentar firmar nuevamente.'
              )
              this.signatureCancel()
            }
            return
          })
        //this.trySignature()
      }, 1e3)
    }
  }
  printInfo() {
    if ('Windows' === this.OSName) {
      console.log('***CONFIGURACI\xd3N PARA SISTEMA OPERATIVO WINDOWS***')
      console.log(
        'Revisar instalaciones y configuraciones a realizar para que el componentre web funcione: https://apps.firmaperu.gob.pe/web/firmador.xhtml'
      )
      console.log('***PLUGIN DE CLICKONCE***')
      console.log(
        'Para que la aplicaci\xf3n de Firma Per\xfa en aplicaciones web funcione desde la integraci\xf3n realizada por los desarrolladores de las entidades, es importante tener instalado en el navegador el plugin de ClickOnce.'
      )
      console.log(
        'Si un plugin de ClickOnce correspondiente al navegador est\xe1 instalado se iniciar\xe1 autom\xe1ticamente el servicio, caso contrario instale un Plugin de las opciones a continuaci\xf3n:'
      )
      console.log('Plugin para Firefox (Instalar solo uno de los listados)')
      console.log(' - https://addons.mozilla.org/es/firefox/addon/logalty-clickonce/')
      console.log(' - https://addons.mozilla.org/es/firefox/addon/breez-clickonce/')
      console.log('Plugin para Google Chrome (Instalar solo uno de los listados)')
      console.log(
        ' - https://chrome.google.com/webstore/detail/windows-remix-clickonce-h/dgpgholdldjjbcmpeckiephjigdpikan'
      )
      console.log(
        ' - https://chrome.google.com/webstore/detail/clickonce-for-google-chro/kekahkplibinaibelipdcikofmedafmb'
      )
      console.log(
        'Para Microsoft Edge basado en Chromium, las ultima versiones trabajan de manera nativa, en caso no se realice la ejecuci\xf3n, puede instalar uno de los plugin de Google Chrome, Para esto tendr\xe1 que aceptar el mensaje de instalaci\xf3n de componentes desde tiendas de terceros que apare al momento de la instalaci\xf3n.'
      )
      console.log(
        'Al instalar un plugin se descargar\xe1 un archivo exe, es importante que este archivo exe se ejecute para que la instalaci\xf3n del plugin sea correcta. En caso sea necesario, habilitar la ejecuci\xf3n de este exe en caso el antivirus muestre un mensaje.'
      )
      console.log(
        'Si luego de tener un plugin instalado y no se ejecuta el servicio, aseg\xfarese que la PC tiene acceso al puerto ' +
          this.port +
          ' que el desarrollador estableci\xf3 para la ejecuci\xf3n del servicio local y que la URL ' +
          this.host +
          ' no est\xe9 bloqueada por alg\xfan programa local como antivirus o firewall.'
      )
      console.log(
        'Puede revisar el log de ejecuci\xf3n en USER_HOME/PCM/FirmadorClienteWeb/log/FirmadorClienteWeb.log'
      )
      console.log('\n')
    } else if ('macOS' === this.OSName) {
      console.log('***CONFIGURACI\xd3N PARA SISTEMA OPERATIVO MACOS***')
      console.log(
        'Revisar instalaciones y configuraciones a realizar para que el componente web funcione: https://apps.firmaperu.gob.pe/web/firmador.xhtml'
      )
      console.log(
        'Si luego de realizar las instalaciones y configuraciones el comoponente web no carga, aseg\xfarese que la PC tiene acceso al puerto ' +
          this.port +
          ' que el desarrollador estableci\xf3 para la ejecuci\xf3n del servicio local y que la URL ' +
          this.host +
          ' no est\xe9 bloqueada por alg\xfan programa local.'
      )
      console.log(
        'Puede revisar el log de ejecuci\xf3n en USER_HOME/PCM/FirmadorClienteWeb/log/FirmadorClienteWeb.log'
      )
    } else if ('Linux' === this.OSName) {
      console.log('***CONFIGURACI\xd3N PARA SISTEMA OPERATIVO LINUX***')
      console.log(
        'Revisar instalaciones y configuraciones a realizar para que el componente web funcione: https://apps.firmaperu.gob.pe/web/firmador.xhtml'
      )
      console.log(
        'Si luego de realizar las instalaciones y configuraciones el comoponente web no carga, aseg\xfarese que la PC tiene acceso al puerto ' +
          this.port +
          ' que el desarrollador estableci\xf3 para la ejecuci\xf3n del servicio local y que la URL ' +
          this.host +
          ' no est\xe9 bloqueada por alg\xfan programa local.'
      )
      console.log(
        'Puede revisar el log de ejecuci\xf3n en USER_HOME/PCM/FirmadorClienteWeb/log/FirmadorClienteWeb.log'
      )
    }
  }
  runService() {
    //aqui se lanza la instalacion del firmaperu
    console.log('Ejecutando la aplicaci\xf3n Firma Per\xfa Cliente Web.')
    //let objFrame=document.getElementById("componentFirmaPeru")
    let div = document.createElement('div')
    div.innerHTML =
      '<iframe id="firmaPeru" style="height:0%;width:0%; border:none;display:none;" src="' +
      this.AppFirmaPeru +
      '?port=' +
      this.port +
      '"></iframe>'
    document.getElementById('addComponent').appendChild(div)
    setTimeout(function () {
      let objFrame = document.getElementById('firmaPeru')
      let padre = objFrame.parentNode
      padre.removeChild(objFrame)
    }, 2e3)
  }
  processSignature() {
    let param = this.getParams()
    console.log('Firma Per\xfa Cliente Web instalado en la PC.')
    console.log('Iniciando operaci\xf3n de firma digital.')
    console.log({ method: 'POST', url: this.host, data: { param }, timeout: 0 })
    const params = new URLSearchParams()
    params.append('param', param)
    axios
      .post(this.host, 'param=' + param, {
        headers: {
          Accept: '*/*',
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        crossdomain: true
      })
      .then((r) => {
        console.log('done')
        if ('ok' === r.data.status) {
          console.log('Operaci\xf3n de firma digital terminada.')
          this.signatureOk()
        } else {
          console.log('Firma Per\xfa Cliente Web: ' + r.data.message)
          console.log(
            'Firma Per\xfa Cliente Web: Para m\xe1s informaci\xf3n revise el log de ejecuci\xf3n en USER_HOME/PCM/FirmadorClienteWeb/FirmadorClienteWeb.log'
          )
          if ('La operaci\xf3n de firma digital fue cancelada.' !== r.data.message) {
            alert('Firma Per\xfa Cliente Web\n\n' + r.data.message)
            this.signatureCancel()
          }
        }
      })
      .catch(() => {
        console.log('Firma Per\xfa Cliente Web: No se pudo hacer POST a ' + this.host)
        alert('Firma Per\xfa Cliente Web\n\nNo se pudo hacer POST a ' + this.hoste)
      })
  }
}
