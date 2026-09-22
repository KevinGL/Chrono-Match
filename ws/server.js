import 'dotenv/config';
import { WebSocketServer } from "ws";
import jwt from "jsonwebtoken";
import url from 'url';

const wss = new WebSocketServer({ port: 8080 });

console.log("Serveur WebSocket démarré sur ws://localhost:8080");

let users = new Map();

wss.on('connection', (ws, request) => {
    console.log('Nouveau client connecté !');

    const query = url.parse(request.url, true).query;
    const token = query.token;

    try
    {
        const decoded = jwt.verify(token, process.env.JWT_KEY);
        users.set(decoded.iat, { "username": decoded.username, "gender": decoded.gender, "search": decoded.search });

        //console.log(users);
    }
    catch (err)
    {
        ws.close(4001, "Authentification échouée");
    }

    /*ws.on('message', (message) => {
        const data = JSON.parse(message);
        console.log(data);

        if(data.type === "connect")
        {
            //
        }
    });*/
});