var http = require('http');
var express = require('express');
var ShareDB = require('sharedb');
var richText = require('rich-text');
var WebSocket = require('ws');
var WebSocketJSONStream = require('websocket-json-stream');

ShareDB.types.register(richText.type);
var backend = new ShareDB();
var connection = backend.connect();


createDoc('realTime');
createDoc('realTime1');
startServer();

// Create initial document then fire callback
function createDoc(docName) {
  var doc = connection.get(docName);
  doc.fetch(function(err) {
    if (err) throw err;
    if (doc.type === null) {
      doc.create([{insert: ""}], 'rich-text');
      return;
    }
  });
}

function startServer() {
  // Create a web server to serve files and listen to WebSocket connections
  var app = express();
  app.use(express.static('static'));
  app.use(express.static('node_modules/quill/dist'));
  var server = http.createServer(app);
  // Connect any incoming WebSocket connection to ShareDB
  var wss = new WebSocket.Server({server: server});
  wss.on('connection', function(ws, req) {
    var stream = new WebSocketJSONStream(ws);
    backend.listen(stream);
  });
  server.listen(8080);
  console.log('Listening on http://localhost:8000');
}
