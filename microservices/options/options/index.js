require('dotenv').config();
const mongoose =  require("mongoose");

let createError = require('http-errors');
let express = require('express');
let path = require('path');
let cookieParser = require('cookie-parser');
let bodyParser = require('body-parser')
let logger = require('morgan');
const router = require('./routes');

mongoose.connect(process.env.mongoURI, {
  // useNewUrlParser: true,
  // useUnifiedTopology: true,
})
.then(res => console.log(`Connection successfully ${res}`))
.catch(err => console.log(`Error in DB connection ${err}`));

let app = express()
let port = 3333

app.use(bodyParser({limit: "20mb"}));
app.use(logger('dev'));
app.use(express.json());
app.use(express.urlencoded({ extended: false }));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));
app.use(router)

app.get('/', (req, res) => {
  res.send('options')
})

app.listen(port, () => {
  console.log(`Example app listening on port ${port}`)
})
