<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request; // ⭐️ (เพิ่ม) Import Request

class ArticleController extends Controller
{
    // ⭐️ (เพิ่ม) รับ Request $request
    public function index(Request $request) 
    {
        // 1. สร้าง Query Builder ขึ้นมา
        $query = Article::where('public_date', '<=', now());

        // ⭐️ (เพิ่ม) ตรวจสอบและเพิ่มเงื่อนไขการค้นหา
        if ($request->has('search') && $request->input('search') != '') {
            $search = $request->input('search');
            // ค้นหาจาก title หรือ body
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('body', 'like', '%' . $search . '%');
            });
        }

        // 2. โหลด Relationship ล่วงหน้าเพื่อประสิทธิภาพ
        $query->with('featureArticle');

        // 3. ใช้ subquery เพื่อเรียงบทความ Feature ขึ้นก่อน
        $query->orderByRaw("
            EXISTS (
                SELECT 1 FROM feature_articles
                WHERE feature_articles.article_id = articles.id
            ) DESC
        ");

        // 4. เรียงลำดับที่เหลือตามวันที่เผยแพร่
        $query->orderBy('public_date', 'desc');

        // 5. ทำการ paginate และ (⭐️ เพิ่ม) withQueryString() เพื่อให้จำค่า search
        $articles = $query->paginate(5)->withQueryString(); 

        return view('article.index',[
            'articles' => $articles
        ]);
    }

    public function showByTag(string $tag_slug)
    {
        $articles = Article::where('public_date', '<=', now())
            ->whereRaw(
                'JSON_SEARCH(tags, "one", ?, NULL, "$[*].tag_slug") IS NOT NULL',
                [$tag_slug]
            )
            ->orderBy('public_date', 'desc')
            ->paginate(10)
            ->withQueryString(); // ⭐️ (เพิ่ม) withQueryString()

            $tagName = $tag_slug; 
            $firstArticle = $articles->first();
            if ($firstArticle) {
                foreach ($firstArticle->tags as $tag) {
                    if ($tag['tag_slug'] === $tag_slug) {
                        $tagName = $tag['tag'];
                        break;
                    }
                }
            }

            return view('article.index', [
                'articles' => $articles,
                'pageTitle' => 'บทความในแท็ก: ' . $tagName,
                'pageSubtitle' => 'รวมบทความทั้งหมดที่เกี่ยวกับ "' . $tagName . '"'
            ]);
    }

    public function show(string $slug)
    {  
        $article = Article::where('slug', $slug)
            ->where('public_date', '<=', now())
            ->firstOrFail();

        // ⭐️ (แก้) เปลี่ยน view 'detail' เป็น 'article.detail' ให้ตรงตามโครงสร้าง (ถ้าไฟล์คุณอยู่ที่
        // resources/views/article/detail.blade.php)
        // ถ้าไฟล์อยู่ที่ resources/views/detail.blade.php ให้ใช้ 'detail' เหมือนเดิม
        return view('article.detail', [ 
            'article' => $article
        ]);
    }
}
