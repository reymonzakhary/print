require('dotenv').config();
const mongoose =  require("mongoose");


var createError = require('http-errors');
var express = require('express');
var path = require('path');
var cookieParser = require('cookie-parser');
var logger = require('morgan');
const router = require('./routes');

mongoose.connect(process.env.mongoURI, {})
.then(res => console.log(`Connection successfully ${res}`))
.catch(err => console.log(`Error in DB connection ${err}`));

const app = express()
const port = 3333

app.use(logger('dev'));
app.use(express.json());
app.use(express.urlencoded({ extended: false }));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));
app.use(router)

app.get('/', (req, res) => {
  res.send('calculations')
})

app.listen(port, () => {
  console.log(`Example app listening on port ${port}`)
})
