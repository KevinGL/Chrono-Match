export const matchmaking = (users, alreadyTouch) =>
{
    users.forEach((currentUser, id1) =>
    {
        const othersUsers = [];
        users.forEach((otherUser, id2) =>
        {
            const ids = id1 <= id2 ? `${id1}-${id2}` : `${id2}-${id1}`;
            
            if(id1 !== id2 && alreadyTouch.indexOf(ids) === -1)
            {
                if(currentUser.gender !== currentUser.search)
                {
                    if(otherUser.gender !== otherUser.search && currentUser.gender !== otherUser.gender)
                    {
                        othersUsers.push({id: id2, ...otherUser});
                    }
                }

                else
                {
                    if(otherUser.gender === otherUser.search && currentUser.gender === otherUser.gender)
                    {
                        othersUsers.push({id: id2, ...otherUser});
                    }
                }
            }
        });

        if(othersUsers.length)
        {
            const index = Math.round(Math.random() * (othersUsers.length - 1));
            const otherUser = othersUsers[index];

            const ids = id1 <= otherUser.id ? `${id1}-${otherUser.id}` : `${otherUser.id}-${id1}`;
            alreadyTouch.push(ids);
            
            currentUser.ws.send(JSON.stringify({status: "contact", contact: otherUser}));
            otherUser.ws.send(JSON.stringify({status: "contact", contact: currentUser}));
        }
    });
}