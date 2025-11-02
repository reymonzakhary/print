const express = require('express');

const app = express();

app.get('/', (req, res) => {
    res.send('hit');
});

app.listen(8000, () => {
    console.log('Listeners on port 8000');
})