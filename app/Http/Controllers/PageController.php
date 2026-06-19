<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function process(Page $page)
    {
        $image = Storage::disk('public')->get($page->image_path);

        $base64 = base64_encode($image);

        $response = Http::timeout(160)->withToken(env('GAPGPT_API_KEY'))
            ->post('https://api.gapgpt.app/v1/responses', [
                'model' => 'gpt-5.2',
                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_image',
                                'image_url' => "data:image/jpeg;base64,{$base64}",
                            ],
                            [
                                'type' => 'input_text',
                                'text' => '
                                    متن موجود در این تصویر را با بالاترین دقت استخراج کن.
                                    قوانین:
                                    - فقط متن داخل تصویر را خروجی بده و هیچ توضیح اضافه‌ای ننویس.
                                    - متن را به زبان اصلی تصویر (فارسی) حفظ کن.
                                    - ترتیب خطوط، پاراگراف‌ها و ساختار متن را تا حد ممکن حفظ کن.
                                    - اگر تیتر، شماره صفحه، جدول یا لیست وجود دارد، ساختار آن را حفظ کن.
                                    - هیچ کلمه‌ای را خلاصه، اصلاح یا بازنویسی نکن.
                                    - اگر بخشی از متن خوانا نیست، آن را با [نامشخص] مشخص کن.
                                    - علائم نگارشی فارسی را حفظ کن.
                                    - اعداد فارسی و انگلیسی را همان‌طور که در تصویر هستند نگه دار.
                                    خروجی فقط متن استخراج‌شده باشد.
                                ',
                            ],
                        ],
                    ],
                ],
            ]);

        $data = $response->json();

        $text = data_get($data, 'output.0.content.0.text', '');

        $page->update([
            'content' => $text,
        ]);

        return response()->json([
            'content' => $text,
        ]);
    }
}
