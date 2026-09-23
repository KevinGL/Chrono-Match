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
            
            currentUser.ws.send(JSON.stringify({status: "contact", contact: otherUser}));
            otherUser.ws.send(JSON.stringify({status: "contact", contact: currentUser}));

            //rooms.push([currentUser, otherUser]);
            rooms.push({user1: currentUser, user2: otherUser, ts: Date.now()});
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
            
            room.user1.ws.send(JSON.stringify({status: "disconnect"}));
            room.user2.ws.send(JSON.stringify({status: "disconnect"}));
            
            const index = rooms.indexOf(room);
            rooms.splice(index, 1);
        }
    });
}