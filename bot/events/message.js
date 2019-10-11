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

    const args = message.content.slice(client.config.prefix.length).trim().split(/ +/g);
    let command = args.shift().toLowerCase();

    if (command === 'signup') command = 'attend';
    if (command === 'signoff') command = 'unattend';

    const https = require('https');

    const data = {
        userId: message.author.id,
        channelId: message.channel.id,
        guildId: message.guild.id,
        query: args.join(' '),
        command: '!'+command
    };
    const requestData = JSON.stringify(data);

    const options = {
        host: client.config.host,
        path: "https://"+client.config.host+'/api/discord/bot',
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Content-Length': requestData.length,
            Authorization: 'Basic '+ new Buffer(client.config.authToken).toString('base64'),
        },
    };

    console.log(message.content);

    var req = https.request(options, (res) => {
        console.log('statusCode:', res.statusCode);
        console.log('headers:', res.headers);

        res.on('data', (d) => {
            process.stdout.write(d);
        });
    });

    req.on('error', (e) => {
        console.error(e);
    });

    req.write(requestData);
    req.end();
};