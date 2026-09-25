import crypto from 'crypto';

function encryptId(id)
{
    const key = Buffer.from(process.env.SECRET_KEY, 'utf-8'); // 16 bytes
    const iv = Buffer.from(process.env.IV_KEY, 'utf-8');     // 12 bytes
    
    const cipher = crypto.createCipheriv('aes-128-gcm', key, iv);
    
    let encrypted = cipher.update(id.toString(), 'utf8', 'hex');
    encrypted += cipher.final('hex');
    
    const tag = cipher.getAuthTag().toString('hex'); // 16 bytes
    
    return `${encrypted}:${tag}`;
}

export function decryptId(encryptedData)
{
    try {
        const key = Buffer.from(process.env.SECRET_KEY, 'utf-8'); // 16 bytes
        const iv = Buffer.from(process.env.IV_KEY, 'utf-8');     // 12 bytes

        const [encrypted, tagHex] = encryptedData.split(':');
        
        if (!encrypted || !tagHex) {
            throw new Error('Format de chaîne chiffrée invalide.');
        }

        const decipher = crypto.createDecipheriv('aes-128-gcm', key, iv);
        
        decipher.setAuthTag(Buffer.from(tagHex, 'hex'));

        let decrypted = decipher.update(encrypted, 'hex', 'utf8');
        decrypted += decipher.final('utf8');

        return isNaN(decrypted) ? decrypted : parseInt(decrypted, 10);
    } catch (error) {
        console.error('Erreur lors du déchiffrement :', error.message);
        return null;
    }
}

export const matchmaking = (users, alreadyTouch, rooms) =>
{
    users.forEach((currentUser, id1) =>
    {
        const othersUsers = [];
        users.forEach((otherUser, id2) =>
        {
            const ids = id1 <= id2 ? `${id1}-${id2}` : `${id2}-${id1}`;
            
            if(id1 !== id2 && alreadyTouch.indexOf(ids) === -1 && currentUser.available && otherUser.available)
            {
                if(currentUser.gender !== currentUser.search)
                {
                    if(otherUser.gender !== otherUser.search && currentUser.gender !== otherUser.gender)
                    {
                        othersUsers.push({id: id2, user :otherUser});
                    }
                }

                else
                {
                    if(otherUser.gender === otherUser.search && currentUser.gender === otherUser.gender)
                    {
                        othersUsers.push({id: id2, user: otherUser});
                    }
                }
            }
        });

        if(othersUsers.length)
        {
            const index = Math.round(Math.random() * (othersUsers.length - 1));
            const otherUser = othersUsers[index].user;
            const id2 = othersUsers[index].id;

            const ids = id1 <= id2 ? `${id1}-${id2}` : `${id2}-${id1}`;
            alreadyTouch.push(ids);

            currentUser.available = false;
            otherUser.available = false;
            
            currentUser.ws.send(JSON.stringify({status: "contact", contact: {...otherUser, id: encryptId(id2)}}));
            otherUser.ws.send(JSON.stringify({status: "contact", contact: {...currentUser, id: encryptId(id1)}}));

            //rooms.push([currentUser, otherUser]);
            rooms.push({user1: {...currentUser, id: id1}, user2: {...otherUser, id: id2}, ts: Date.now()});
        }
    });
}

export const manageTiming = (rooms) =>
{
    rooms.map((room) =>
    {
        const delay = (Date.now() - room.ts) / 1000;
        if(delay > 180.0)
        {
            room.user1.available = true;
            room.user2.available = true;
            
            room.user1.ws.send(JSON.stringify({status: "disconnect", contact: encryptId(room.user2.id)}));
            room.user2.ws.send(JSON.stringify({status: "disconnect", contact: encryptId(room.user1.id)}));
            
            const index = rooms.indexOf(room);
            rooms.splice(index, 1);
        }
    });
}