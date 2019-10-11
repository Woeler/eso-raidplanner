module.exports = (client, message) => {
    // Ignore all bots
    if (message.author.bot) return;

    if (message.guild === undefined || message.guild === null) {
        return;
    }

    if (!message.channel.permissionsFor(message.guild.me).has("EMBED_LINKS", false) || !message.channel.permissionsFor(message.guild.me).has("SEND_MESSAGES", false)) {
        return;
    }

    // Ignore messages not starting with the prefix (in config.json)
    if (message.content.indexOf(client.config.prefix) !== 0) return;

    const https = require('https');

    const data = {
        userId: message.author.id,
        channelId: message.channel.id,
        guildId: message.guild.id,
        text: message.content
    };

    const options = {
        host: client.config.host,
        path: client.config.host+'/api/discord/bot',
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Authorization: 'Basic '+ new Buffer(client.config.authToken).toString('base64'),
        },
    };

    const requestData = JSON.stringify(data);

    let serverResponse = '';
    const request = https.request(options, res => {
        res.on('data', chunk => {
            serverResponse += chunk;
        });
        res.on('end', () => {
            callback(serverResponse);
        });
    });

    request.write(requestData);
};