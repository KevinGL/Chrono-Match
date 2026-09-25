import 'dotenv/config';
import { WebSocketServer } from "ws";
import jwt from "jsonwebtoken";
import url from 'url';
import { decryptId } from './functions.js';

const wss = new WebSocketServer({ port: 5000 });

console.log("Serveur messagerie démarré sur ws://localhost:5000");

let users = new Map();

wss.on('connection', (ws, request) => {
    const query = url.parse(request.url, true).query;
    const token = query.token;

    try
    {
        const decoded = jwt.verify(token, process.env.JWT_KEY);
        users.set(decoded.id, ws);
    }
    catch (err)
    {
        ws.close(4001, "Authentification échouée");
    }

    ws.on('message', (message) => {
        const data = JSON.parse(message);

        if(data.type === "message")
        {
            const message = data.message;
            const username = data.username;
            const contactId = decryptId(data.contact);

            const contact = users.get(contactId);
            contact.send(JSON.stringify({message, username}));
        }
    });
});