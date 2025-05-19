import { __ } from '@wordpress/i18n';
import {
    InspectorControls,
    useBlockProps,
} from '@wordpress/block-editor';
import {
    PanelBody,
    TextControl,
    Button,
    Spinner
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
    const { postId, postTitle, postLink } = attributes;

    const [search, setSearch] = useState('');
    const [results, setResults] = useState([]);
    const [page, setPage] = useState(1);
    const [maxPages, setMaxPages] = useState(1);
    const [loading, setLoading] = useState(false);

    const blockProps = useBlockProps();

    const fetchPosts = () => {
        setLoading(true);
        const query = new URLSearchParams({ search, page });

        apiFetch({ path: `/rmlb/v1/search-posts?${query}` })
            .then((data) => {
                setResults(data.posts || []);
                setMaxPages(data.max_pages || 1);
            })
            .catch((err) => {
                console.error('Failed to fetch posts:', err);
                setResults([]);
                setMaxPages(1);
            })
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        fetchPosts();
    }, [search, page]);

    const selectPost = (post) => {
        setAttributes({
            postId: post.id,
            postTitle: post.title,
            postLink: post.link,
        });
    };

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title="Select a Post" initialOpen={true}>
                    <TextControl
                        label="Search Posts"
                        value={search}
                        onChange={(val) => {
                            setPage(1);
                            setSearch(val);
                        }}
                    />
                    {loading ? (
                        <Spinner />
                    ) : (
                        <>
                            {results.length > 0 ? (
                                results.map((post) => (
                                    <Button
                                        key={post.id}
                                        isSecondary
                                        onClick={() => selectPost(post)}
                                        style={{ display: 'block', margin: '4px 0' }}
                                    >
                                        {post.title || `(No title)`} {/* fallback title */}
                                    </Button>
                                ))
                            ) : (
                                <p>No posts found.</p>
                            )}
                            <div style={{ marginTop: '10px' }}>
                                <Button
                                    disabled={page <= 1}
                                    onClick={() => setPage((p) => p - 1)}
                                >
                                    Previous
                                </Button>
                                <Button
                                    disabled={page >= maxPages}
                                    onClick={() => setPage((p) => p + 1)}
                                    style={{ marginLeft: '10px' }}
                                >
                                    Next
                                </Button>
                            </div>
                        </>
                    )}
                </PanelBody>
            </InspectorControls>

            {postId ? (
                <p className="dmg-read-more">
                    Read more: <a href={postLink}>{postTitle}</a>
                </p>
            ) : (
                <p>Select a post from the sidebar</p>
            )}
        </div>
    );
}
