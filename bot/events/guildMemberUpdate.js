module.exports = (client, oldMember, newMember) => {
    if (oldMember.displayName === newMember.displayName) {
        return;
    }

    const https = require('https');

    const data = {
        userId: newMember.user.id,
        guildId: newMember.guild.id,
        userNick: encodeURI(newMember.displayName)
    };

    const requestData = JSON.stringify(data);

    const options = {
        host: client.config.host,
        path: "https://"+client.config.host+'/api/discord/nickname',
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Content-Length': requestData.length,
            'Authorization': 'Bearer '+ client.config.authToken,
        },
    };

    let req = https.request(options, (res) => {
        res.on('data', (d) => {

        });
        req.on('error', (e) => {
            console.error(e);
        });
    });

    req.write(requestData);
    req.end();
};
