module.exports = (client, message) => {
    // Ignore all bots
    if (message.author.bot) return;

    if (message.guild === undefined || message.guild === null) {
        return;
    }

    // Ignore messages not starting with the prefix (in config.json)
    if (message.content.indexOf(client.config.prefix) !== 0) return;

    if (!message.channel.permissionsFor(message.guild.me).has("SEND_MESSAGES", false)) {
        return;
    }

    const https = require('https');

    const data = {
        userId: message.author.id,
        channelId: message.channel.id,
        guildId: message.guild.id,
        args: message.content
    };

    const requestData = JSON.stringify(data);

    const options = {
        host: client.config.host,
        path: "https://"+client.config.host+'/api/discord/bot',
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Content-Length': requestData.length,
            'Authorization': 'Bearer '+ client.config.authToken,
        },
    };

    let req = https.request(options, (res) => {
        let interim = '';
        res.on('data', (d) => {
            if (d !== undefined) {
                interim += d;
            }
        });
        res.on('error', (e) => {
            console.error(e);
        });
        res.on('end', () => {
            if (res.statusCode === 200) {
                if (interim !== undefined) {
                    try {
                        if (!message.channel.permissionsFor(message.guild.me).has("EMBED_LINKS", false)) {
                            message.channel.send('I do not have the "embed links" permissions in this channel I need those to function correctly.')
                            return;
                        }
                        message.channel.send(JSON.parse(interim));
                    } catch (e) {
                        message.channel.send('An error occurred. The team has been notified.');
                        console.log(message.content)
                        console.log(e);
                    }
                }
            }
            if (res.statusCode >= 400) {
                console.log(message.content)
                console.log('Status: '+res.statusCode);
            }
        });
    });

    req.write(requestData);
    req.end();
};
