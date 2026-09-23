import 'dotenv/config';
import { WebSocketServer } from "ws";
import jwt from "jsonwebtoken";
import url from 'url';
import { matchmaking } from './functions.js';

const wss = new WebSocketServer({ port: 8080 });

console.log("Serveur WebSocket démarré sur ws://localhost:8080");

let users = new Map();
let alreadyTouch = [];
let rooms = [];

setInterval(() => matchmaking(users, alreadyTouch, rooms), 1000);

wss.on('connection', (ws, request) => {
    console.log('Nouveau client connecté !');

    const query = url.parse(request.url, true).query;
    const token = query.token;

    try
    {
        const decoded = jwt.verify(token, process.env.JWT_KEY);
        users.set(decoded.id, { username: decoded.username, gender: decoded.gender, search: decoded.search, ws });

        ws.send(JSON.stringify({status: "waiting"}));
    }
    catch (err)
    {
        ws.close(4001, "Authentification échouée");
    }

    ws.on('message', (message) => {
        const data = JSON.parse(message);

        if(data.type === "message")
        {
            const index = rooms.findIndex((room) =>
            {
                return room[0].ws === ws || room[1].ws === ws;
            });

            if(index > -1)
            {
                if(rooms[index][0].ws === ws)
                {
                    rooms[index][1].ws.send(JSON.stringify({status: "receive", username: rooms[index][0].username, message: data.message}));
                }

                else
                if(rooms[index][1].ws === ws)
                {
                    rooms[index][0].ws.send(JSON.stringify({status: "receive", username: rooms[index][1].username, message: data.message}));
                }
            }
        }
    });
});