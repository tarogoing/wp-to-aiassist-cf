#  WP to AIAssist via Cloudflare Worker

### WordPress → Cloudflare Worker → AI / Telegram 自动化桥接插件

（English version included below）


# 🇨🇳 简体中文说明

## 这个插件是什么？

**WP to AIAssist via Cloudflare Worker**
是一个 WordPress 自动化插件，当文章首次发布时，会自动将文章信息（标题、摘要、链接、封面图等）以 JSON 形式推送到 **Cloudflare Worker**，由 Worker 进行进一步处理，例如：

* AI 自动翻译
* 推送到 Telegram 频道
* 触发其他自动化任务（存档、Webhook 转发、队列系统等）

插件本身不做 AI，也不发送 Telegram，而是负责把 WordPress 的文章事件“干净地”传给 Cloudflare Worker，实现 **完全可扩展的外部处理链路**。

##  功能特点

*  **文章首次发布时自动触发**
*  **非阻塞发送**：Worker 挂了也不会影响 WordPress 发布
*  **支持 Secret 校验**，防止第三方伪造请求
*  自动携带：标题、摘要、链接、封面图、分类标签、时间戳等
*  完全自托管，Cloudflare Worker 免费额度即可使用
*  极其轻量、无任何第三方依赖


## 安装方式

### 方法 1：WordPress 后台直接上传（推荐）

1. 下载本项目 ZIP
2. WordPress 后台 → 插件 → 安装插件 → 上传插件
3. 上传 `wp-to-aiassist-cf.zip`
4. 安装并启用

### 方法 2：手动上传

将目录上传到：

```
/wp-content/plugins/wp-to-aiassist-cf/
```

启用即可。


##  配置步骤

进入 WordPress 后台：

> 设置 → WP → AIAssist CF

配置两项参数：

### 1. Cloudflare Worker Endpoint

填写你的 Worker URL，例如：

```
https://your-worker.yourdomain.workers.dev
```

### 2. Secret

用于 WordPress → Worker 的身份验证，在 Worker 中使用：

```js
if (json.secret !== env.WP_SECRET) return new Response("Unauthorized", { status: 401 });
```


##  WordPress 发送的 JSON Payload

发布文章时 WordPress 会向 Worker POST：

```json
{
  "post_id": 12,
  "title": "Hello World",
  "url": "https://example.com/hello-world",
  "excerpt": "This is the excerpt…",
  "image": "https://example.com/wp-content/uploads/cover.jpg",
  "categories": ["News"],
  "tags": ["update"],
  "date_gmt": "2025-01-01T10:00:00Z",
  "date_local": "2025-01-01T18:00:00+08:00",
  "secret": "your_secret_here",
  "site_url": "https://example.com"
}
```

Worker 收到后即可进行任意处理。


## 使用示例（Cloudflare Worker）

基础 Worker（校验 + 输出）：

```js
export default {
  async fetch(request, env) {
    const data = await request.json();

    if (data.secret !== env.WP_SECRET) {
      return new Response("Unauthorized", { status: 401 });
    }

    // 在这里做你的逻辑：
    // AI 翻译 / Telegram 推送 / 存入 KV / 日志 / Webhook 转发...
    console.log("Received post:", data.title);

    return new Response("OK");
  }
}
```


##  许可证

MIT License


# 🇺🇸 English Version

##  What is this plugin?

**WP to AIAssist via Cloudflare Worker**
is a lightweight WordPress automation plugin that sends post data to a **Cloudflare Worker** whenever a post is published. This allows your Worker to perform any custom processing, such as:

* AI translation
* Sending messages to a Telegram channel
* Triggering automation pipelines, webhooks, queues, etc.

The plugin itself does *not* translate or send Telegram messages —
it simply provides a clean bridge between WordPress and Cloudflare Worker.


##  Features

*  Fires only when a post is *first published*
*  **Non-blocking request** — WordPress never slows down even if Worker fails
*  **Secret validation** to prevent spoofed requests
*  Sends enriched metadata: title, excerpt, featured image, categories, tags, timestamps
*  Works perfectly with Cloudflare Worker free tier
*  Super lightweight, no dependencies

##  Installation

### Option 1: Upload via WordPress Admin

1. Download the ZIP
2. WordPress → Plugins → Add New → Upload Plugin
3. Select `wp-to-aiassist-cf.zip`
4. Install & activate

### Option 2: Manual Upload

Upload the folder to:

```
/wp-content/plugins/wp-to-aiassist-cf/
```

Activate via Admin Panel.


##  Configuration

Go to:

**Settings → WP → AIAssist CF**

Configure:

### 1. Cloudflare Worker Endpoint

Example:

```
https://your-worker.yourdomain.workers.dev
```

### 2. Secret

Use the same secret in your Worker:

```js
if (json.secret !== env.WP_SECRET) return new Response("Unauthorized", { status: 401 });
```

##  JSON Payload Sent by WordPress

WordPress POSTs the following data:

```json
{
  "post_id": 12,
  "title": "Hello World",
  "url": "https://example.com/hello-world",
  "excerpt": "This is the excerpt…",
  "image": "https://example.com/wp-content/uploads/cover.jpg",
  "categories": ["News"],
  "tags": ["update"],
  "date_gmt": "2025-01-01T10:00:00Z",
  "date_local": "2025-01-01T18:00:00+08:00",
  "secret": "your_secret_here",
  "site_url": "https://example.com"
}
```

Your Worker can freely process this data.


##  Example Cloudflare Worker

```js
export default {
  async fetch(request, env) {
    const data = await request.json();

    if (data.secret !== env.WP_SECRET) {
      return new Response("Unauthorized", { status: 401 });
    }

    // Process the post: AI → Telegram → Logging → Forwarding...
    console.log("Received post:", data.title);

    return new Response("OK");
  }
}
```


## 📄 License

MIT License
