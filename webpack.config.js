const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
module.exports = {
  mode: 'development',
  entry: {
    'js/app' : './src/js/app.js',
    'js/inicio' : './src/js/inicio.js',
    'js/usuario/index' : './src/js/usuario/index.js',
    'js/cliente/index' : './src/js/cliente/index.js',
    'js/marca/index' : './src/js/marca/index.js',
    'js/modelo/index' : './src/js/modelo/index.js',
    'js/inventario/index' : './src/js/inventario/index.js',
    'js/empleado/index' : './src/js/empleado/index.js',
    'js/tiposervicio/index' : './src/js/tiposervicio/index.js',
    'js/recepcion/index' : './src/js/recepcion/index.js',
    'js/ordentrabajo/index' : './src/js/ordentrabajo/index.js',
    'js/venta/index' : './src/js/venta/index.js',
    'js/login/index' : './src/js/login/index.js',
    'js/aplicacion/index' : './src/js/aplicacion/index.js',
    'js/permiso/index' : './src/js/permiso/index.js',
    'js/asignacion_permiso/index' : './src/js/asignacion_permiso/index.js',
    
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'public/build')
  },
  plugins: [
    new MiniCssExtractPlugin({
        filename: 'styles.css'
    })
  ],
  module: {
    rules: [
      {
        test: /\.(c|sc|sa)ss$/,
        use: [
            {
                loader: MiniCssExtractPlugin.loader
            },
            'css-loader',
            'sass-loader'
        ]
      },
      {
        test: /\.(png|svg|jpe?g|gif)$/,
        type: 'asset/resource',
      },
    ]
  }
};